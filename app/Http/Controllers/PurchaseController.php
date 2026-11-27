<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\LicenseEvent;
use App\Models\LicenseProduct;
use App\Models\PurchaseOrder;
use App\Models\Setting;
use App\Services\PurchaseService;
use App\Support\LicenseDuration;
use App\Support\ListFilters;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function shop(Request $request): View
    {
        $license = $request->attributes->get('member_license');

        $filters = ListFilters::read($request, ['status' => ['nullable', 'in:pending,paid,cancelled']]);
        $query = PurchaseOrder::with('issuedLicense')->where('owner_license_id', $license->id);
        ListFilters::search($query, $filters, ['number', 'product_name']);
        ListFilters::dates($query, $filters);
        if ($filters['status'] ?? null) {
            $query->where('status', $filters['status']);
        }
        ListFilters::sort($query, $filters, 'product_name', 'amount');

        return view('member.shop', ['license' => $license, 'products' => LicenseProduct::where('is_active', true)->orderBy('price')->paginate(8, ['*'], 'catalog_page')->withQueryString(), 'orders' => $query->paginate($filters['per_page'] ?? 10)->withQueryString(), 'games' => Game::all()->keyBy('id'), 'renewalUrl' => Setting::renewalUrl($license)]);
    }

    public function order(Request $request, PurchaseService $service): RedirectResponse
    {
        $data = $request->validate(['product_id' => ['required', 'integer', 'exists:license_products,id'], 'idempotency_key' => ['required', 'uuid']]);
        $order = $service->order($request->attributes->get('member_license'), $data['product_id'], $data['idempotency_key'], $request->ip());

        return redirect()->route('member.shop')->with('success', 'Pesanan '.$order->number.' dibuat. Menunggu verifikasi pembayaran admin.');
    }

    public function index(Request $request): View
    {
        $filters = ListFilters::read($request, ['status' => ['nullable', 'in:pending,paid,cancelled'], 'method' => ['nullable', 'in:manual,bank_transfer,e_wallet,qris,cash'], 'purpose' => ['nullable', 'in:purchase,renewal']]);
        $query = PurchaseOrder::with(['owner', 'issuedLicense']);
        ListFilters::search($query, $filters, ['number', 'member_name', 'product_name', 'payment_reference', 'payment_provider', 'payer_name', 'customer_contact']);
        ListFilters::dates($query, $filters);
        foreach (['status' => 'status', 'method' => 'payment_method', 'purpose' => 'purpose'] as $field => $column) {
            if ($filters[$field] ?? null) {
                $query->where($column, $filters[$field]);
            }
        }
        ListFilters::sort($query, $filters, 'member_name', 'amount');

        return view('orders', ['orders' => $query->paginate($filters['per_page'] ?? 10)->withQueryString()]);
    }

    public function products(Request $request): View
    {
        $filters = ListFilters::read($request, ['status' => ['nullable', 'in:active,hidden'], 'game' => ['nullable', 'integer', 'exists:games,id']]);
        $query = LicenseProduct::query();
        ListFilters::search($query, $filters, ['name', 'description']);
        ListFilters::dates($query, $filters);
        if ($filters['status'] ?? null) {
            $query->where('is_active', $filters['status'] === 'active');
        }
        if ($filters['game'] ?? null) {
            $query->whereJsonContains('game_ids', (int) $filters['game']);
        }
        ListFilters::sort($query, $filters, 'name', 'price');

        return view('products', ['products' => $query->paginate($filters['per_page'] ?? 10)->withQueryString(), 'games' => Game::orderBy('name')->get()]);
    }

    public function product(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100'], 'description' => ['nullable', 'string', 'max:500'], 'price' => ['required', 'integer', 'between:1,100000000000'], 'game_ids' => ['required', 'array', 'min:1', 'max:100'], 'game_ids.*' => ['required', 'integer', 'distinct', 'exists:games,id']] + LicenseDuration::rules($request->all()));
        $data = LicenseDuration::productData($data);
        $product = LicenseProduct::create($data + ['is_active' => true]);
        LicenseEvent::create(['user_id' => $request->user()->id, 'action' => 'product_created', 'ip_address' => $request->ip(), 'metadata' => ['product_name' => $product->name]]);

        return redirect()->route('products.index')->with('success', 'Paket ditambahkan ke katalog member.');
    }

    public function updateProduct(Request $request, LicenseProduct $product): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100'], 'description' => ['nullable', 'string', 'max:500'], 'price' => ['required', 'integer', 'between:1,100000000000'], 'game_ids' => ['required', 'array', 'min:1', 'max:100'], 'game_ids.*' => ['required', 'integer', 'distinct', 'exists:games,id']] + LicenseDuration::rules($request->all()));
        $data = LicenseDuration::productData($data);
        DB::transaction(function () use ($product, $data, $request): void {
            $locked = LicenseProduct::whereKey($product->id)->lockForUpdate()->firstOrFail();
            $locked->update($data);
            LicenseEvent::create(['user_id' => $request->user()->id, 'action' => 'product_updated', 'ip_address' => $request->ip(), 'metadata' => ['product_name' => $locked->name]]);
        });

        return redirect()->route('products.index')->with('success', 'Paket diperbarui. Pesanan lama tetap memakai harga dan akses saat dibuat.');
    }

    public function memberCancel(Request $request, PurchaseOrder $order, PurchaseService $service): RedirectResponse
    {
        abort_unless($order->owner_license_id === $request->attributes->get('member_license')->id, 403);
        $service->cancel($order, null, $request->ip());

        return redirect()->route('member.shop')->with('success', 'Pesanan Anda dibatalkan.');
    }

    public function toggle(Request $request, LicenseProduct $product): RedirectResponse
    {
        DB::transaction(function () use ($product, $request): void {
            $p = LicenseProduct::whereKey($product->id)->lockForUpdate()->firstOrFail();
            $p->update(['is_active' => ! $p->is_active]);
            LicenseEvent::create(['user_id' => $request->user()->id, 'action' => 'product_updated', 'metadata' => ['product_name' => $p->name]]);
        });

        return redirect()->route('products.index')->with('success', 'Status paket diperbarui.');
    }

    public function settle(Request $request, PurchaseOrder $order, PurchaseService $service): RedirectResponse
    {
        $data = $request->validate(['payment_reference' => ['required', 'string', 'max:100'], 'payment_method' => ['required', 'in:bank_transfer,e_wallet,qris,cash'], 'payment_provider' => ['required_if:payment_method,bank_transfer,e_wallet,qris', 'nullable', 'string', 'max:100'], 'payer_name' => ['required', 'nullable', 'string', 'max:100'], 'paid_at' => ['bail', 'required', 'nullable', 'date_format:Y-m-d\TH:i', function (string $attribute, mixed $value, \Closure $fail): void {
            if ($value && CarbonImmutable::parse($value, 'Asia/Jakarta')->isFuture()) {
                $fail('Waktu pembayaran tidak boleh di masa depan.');
            }
        }], 'payment_notes' => ['nullable', 'string', 'max:1000']]);
        $service->settle($order, $request->user()->id, $data['payment_reference'], $request->ip(), $data);

        return redirect()->route('orders.index')->with('success', 'Pembayaran tercatat dan key diterbitkan tepat satu kali.');
    }

    public function cancel(Request $request, PurchaseOrder $order, PurchaseService $service): RedirectResponse
    {
        $service->cancel($order, $request->user()->id, $request->ip());

        return redirect()->route('orders.index')->with('success', 'Pesanan dibatalkan. Tidak ada pendapatan yang dicatat.');
    }

    public function destroy(Request $request, PurchaseOrder $order, PurchaseService $service): RedirectResponse
    {
        $service->deleteUnpaid($order, $request->user()->id, $request->ip());

        return redirect()->route('orders.index')->with('success', 'Invoice yang belum dibayar berhasil dihapus.');
    }
}
