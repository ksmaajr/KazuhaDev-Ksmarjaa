<?php

namespace App\Http\Controllers;

use App\Http\Requests\MemberLoginRequest;
use App\Models\Game;
use App\Models\LicenseKey;
use App\Models\LicenseProduct;
use App\Models\PurchaseOrder;
use App\Models\Setting;
use App\Services\LicenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function login(Request $request): View|RedirectResponse
    {
        $license = LicenseKey::find($request->session()->get('member_license_id'));
        if ($license && ! $license->revoked_at) {
            return redirect()->route('member.dashboard');
        }
        $request->session()->forget('member_license_id');

        return view('member.login');
    }

    public function authenticate(MemberLoginRequest $request): RedirectResponse
    {
        $license = LicenseKey::where('key_hash', hash('sha256', $request->validated('key')))->whereNull('revoked_at')->first();
        if (! $license) {
            return redirect()->route('member.login')->withErrors(['key' => 'Key tidak tersedia atau telah dicabut.']);
        }
        $request->session()->regenerate(true);
        $request->session()->put('member_license_id', $license->id);

        return redirect()->route('member.dashboard');
    }

    public function dashboard(Request $request): View
    {
        $license = $request->attributes->get('member_license');

        $license->load('games');
        $renewalProducts = LicenseProduct::where('is_active', true)->orderBy('price')->get();
        $renewalGameIds = $renewalProducts->flatMap(fn ($product) => $product->game_ids ?? [])->unique()->values();
        $renewalGames = Game::whereIn('id', $renewalGameIds)->get()->keyBy('id');
        $renewalInvoices = PurchaseOrder::where('owner_license_id', $license->id)->where('purpose', 'renewal')->where('status', 'pending')->orderByDesc('id')->get();

        return view('member.dashboard', ['license' => $license, 'renewalUrl' => Setting::renewalUrl($license), 'renewalProducts' => $renewalProducts, 'renewalGames' => $renewalGames, 'renewalInvoices' => $renewalInvoices]);
    }

    public function status(Request $request): JsonResponse
    {
        $license = $request->attributes->get('member_license');

        return response()->json(['success' => true, 'data' => [
            'status' => $license->status(), 'hwid' => $license->hwid, 'reset_count' => $license->reset_count,
            'activated_at' => $license->activated_at?->toIso8601String(), 'last_seen_at' => $license->last_seen_at?->toIso8601String(),
            'expires_at' => $license->expires_at?->toIso8601String(), 'duration_seconds' => $license->duration_seconds,
            'server_time' => now()->toIso8601String(),
        ]]);
    }

    public function reset(Request $request, LicenseService $service): RedirectResponse
    {
        $license = $request->attributes->get('member_license');
        if ($license->status() !== 'active') {
            return redirect()->route('member.dashboard')->withErrors(['hwid' => 'Reset hanya tersedia untuk key aktif yang terikat perangkat.']);
        }
        $service->reset($license, null, $request->ip());

        return redirect()->route('member.dashboard')->with('success', 'HWID dilepas. Login ulang melalui injector untuk mengikat perangkat. Sisa durasi tetap berjalan.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('member_license_id');
        $request->session()->regenerate(true);

        return redirect()->route('member.login');
    }
}
