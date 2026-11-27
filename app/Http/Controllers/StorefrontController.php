<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\LicenseKey;
use App\Models\LicenseProduct;
use App\Models\PurchaseOrder;
use App\Services\PurchaseService;
use App\Services\SumoPodService;
use App\Support\ListFilters;
use App\Support\PaymentSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function index(Request $request): View
    {
        if (! $request->session()->has('checkout_secret')) {
            $request->session()->put('checkout_secret', Str::random(64));
        }
        $filters = ListFilters::read($request, ['game' => ['nullable', 'integer', 'exists:games,id']]);
        $query = LicenseProduct::where('is_active', true);
        ListFilters::search($query, $filters, ['name', 'description']);
        if ($filters['game'] ?? null) {
            $query->where(fn ($q) => $q->whereJsonContains('game_ids', (int) $filters['game'])->orWhereJsonContains('game_ids', (string) $filters['game']));
        }
        ListFilters::sort($query, $filters, 'name', 'price');

        return view('storefront', ['products' => $query->paginate($filters['per_page'] ?? 8)->withQueryString(), 'games' => Game::all()->keyBy('id'), 'renewal' => false, 'license' => null, 'pendingInvoices' => $this->pendingInvoices($request, 'purchase'), 'gatewayReady' => PaymentSettings::ready(), 'gatewayMode' => PaymentSettings::mode()]);
    }

    public function renew(Request $request): View
    {
        $license = $request->attributes->get('member_license');
        $items = LicenseProduct::where('is_active', true)->orderBy('price')->get();
        $page = Paginator::resolveCurrentPage();
        $products = new LengthAwarePaginator($items->forPage($page, 8)->values(), $items->count(), 8, $page, ['path' => $request->url()]);

        return view('storefront', ['products' => $products, 'games' => Game::all()->keyBy('id'), 'renewal' => true, 'license' => $license, 'pendingInvoices' => $this->pendingInvoices($request, 'renewal'), 'gatewayReady' => PaymentSettings::ready(), 'gatewayMode' => PaymentSettings::mode()]);
    }

    public function checkout(Request $request, PurchaseService $service): RedirectResponse
    {
        $productId = $request->validate(['product_id' => ['required', 'integer']])['product_id'];
        if ($pending = $this->pendingInvoices($request, 'purchase')->firstWhere('license_product_id', (int) $productId)) {
            return redirect()->route('checkout.receipt', ['order' => $pending->number]);
        }
        $data = $request->validate(['product_id' => ['required', 'integer', 'exists:license_products,id'], 'idempotency_key' => ['required', 'uuid'], 'member_name' => ['required', 'string', 'max:100'], 'customer_contact' => ['required', 'string', 'max:100'], 'customer_email' => ['nullable', 'email', 'max:255']]);
        if (! $request->session()->has('checkout_secret')) {
            $request->session()->put('checkout_secret', Str::random(64));
        }
        $member = LicenseKey::find($request->session()->get('member_license_id'));
        if ($member && ! $member->revoked_at) {
            $order = $service->order($member, (int) $data['product_id'], $data['idempotency_key'], $request->ip());
            if ($order->wasRecentlyCreated) {
                $order->update(['member_name' => $data['member_name'], 'customer_contact' => $data['customer_contact'], 'customer_email' => $data['customer_email'] ?? null]);
            }
        } else {
            $order = $service->guestOrder($data, (int) $data['product_id'], $data['idempotency_key'], hash('sha256', $request->session()->get('checkout_secret')), $request->ip());
        }

        return redirect()->route('checkout.receipt', ['order' => $order->number]);
    }

    public function renewOrder(Request $request, PurchaseService $service): RedirectResponse
    {
        $productId = $request->validate(['product_id' => ['required', 'integer']])['product_id'];
        if ($pending = $this->pendingInvoices($request, 'renewal')->firstWhere('license_product_id', (int) $productId)) {
            return redirect()->route('checkout.receipt', ['order' => $pending->number]);
        }
        $data = $request->validate(['product_id' => ['required', 'integer', 'exists:license_products,id'], 'idempotency_key' => ['required', 'uuid']]);
        $order = $service->order($request->attributes->get('member_license'), (int) $data['product_id'], $data['idempotency_key'], $request->ip(), true);

        return redirect()->route('checkout.receipt', ['order' => $order->number]);
    }

    private function pendingInvoices(Request $request, string $purpose): \Illuminate\Support\Collection
    {
        $guest = $request->session()->get('checkout_secret');
        $member = LicenseKey::find($request->session()->get('member_license_id'));
        $member = $member && ! $member->revoked_at ? $member : null;
        if (! $guest && ! $member) {
            return collect();
        }
        return PurchaseOrder::where('status', 'pending')->where('purpose', $purpose)
            ->where(function ($query) use ($guest, $member, $purpose): void {
                if ($guest && $purpose === 'purchase') {
                    $query->where('guest_token_hash', hash('sha256', $guest));
                }
                if ($member) {
                    $query->orWhere('owner_license_id', $member->id);
                }
                if (!$member && $purpose !== 'purchase') {
                    $query->whereRaw('1 = 0');
                }
            })->orderByDesc('id')->get();
    }

    private function canAccessOrder(Request $request, PurchaseOrder $order): bool
    {
        $guest = $request->session()->get('checkout_secret');
        $ownGuest = $guest && $order->guest_token_hash && hash_equals($order->guest_token_hash, hash('sha256', $guest));
        $member = LicenseKey::find($request->session()->get('member_license_id'));
        $ownMember = $member && ! $member->revoked_at && $order->owner_license_id === $member->id;
        return $ownGuest || $ownMember || ($request->user() && $request->user()->role === 'admin');
    }

    private function authorizeOrder(Request $request, PurchaseOrder $order): void
    {
        abort_unless($this->canAccessOrder($request, $order), 404);
    }

    public function paymentReturn(Request $request): RedirectResponse
    {
        $number = $request->session()->get('payment_return_order');
        $order = is_string($number) ? PurchaseOrder::where('number', $number)->first() : null;
        if (! $order || ! $order->gateway_payment_id || ! $this->canAccessOrder($request, $order)) {
            $request->session()->forget('payment_return_order');

            return redirect()->route('home')->with('error', 'Detail pembayaran tidak tersedia di sesi ini. Buka kembali invoice asli atau hubungi admin.')->header('Cache-Control', 'private, no-store');
        }

        return redirect()->route('checkout.receipt', ['order' => $order->number])->header('Cache-Control', 'private, no-store');
    }

    public function receipt(Request $request, PurchaseOrder $order): View
    {
        $this->authorizeOrder($request, $order);

        return view('checkout', ['order' => $order->load('issuedLicense'), 'gatewayReady' => PaymentSettings::ready(), 'gatewayMode' => PaymentSettings::mode(), 'gatewayIssue' => PaymentSettings::unavailableReason()]);
    }

    public function payment(Request $request, PurchaseOrder $order, SumoPodService $service): RedirectResponse
    {
        $this->authorizeOrder($request, $order);
        $data = $request->validate(['method' => ['required', 'in:QRIS,QRIS_INSTANT'], 'accept_terms' => ['accepted']], ['accept_terms.accepted' => 'Anda wajib menyetujui Syarat & Ketentuan sebelum melanjutkan pembayaran.']);
        $invoice = $service->createLink($order, $data['method']);
        if ($invoice->gateway_payment_id) {
            $request->session()->put('payment_return_order', $invoice->number);
        }

        return redirect()->route('checkout.receipt', ['order' => $order->number]);
    }

    public function status(Request $request, PurchaseOrder $order): JsonResponse
    {
        $this->authorizeOrder($request, $order);

        return response()->json(['status' => $order->status, 'gateway_status' => $order->gateway_status, 'updated' => $order->updated_at->toIso8601String()])->header('Cache-Control', 'private, no-store');
    }
}
