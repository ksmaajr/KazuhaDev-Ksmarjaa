<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\LicenseEvent;
use App\Models\LicenseKey;
use App\Models\Setting;
use App\Services\FinanceReportService;
use App\Support\ListFilters;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $filters = ListFilters::read($request, ['status' => ['nullable', 'in:active,unbound,expired,revoked'], 'game' => ['nullable', 'integer', 'exists:games,id']]);
        $query = LicenseKey::with('games');
        if ($search = $filters['q'] ?? null) {
            $query->where(function ($query) use ($search): void {
                $query->where('label', 'like', '%'.$search.'%')->orWhere('member_name', 'like', '%'.$search.'%')->orWhere('key_hint', 'like', '%'.$search.'%');
            });
        }
        match ($filters['status'] ?? '') {
            'active' => $query->whereNull('revoked_at')->where('expires_at', '>', now())->whereNotNull('hwid_hash'),
            'unbound' => $query->whereNull('revoked_at')->whereNull('hwid_hash')->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now())),
            'expired' => $query->whereNull('revoked_at')->where('expires_at', '<=', now()),
            'revoked' => $query->whereNotNull('revoked_at'),default => null,
        };
        ListFilters::dates($query, $filters);
        if ($filters['game'] ?? null) {
            $query->whereHas('games', fn ($games) => $games->where('games.id', $filters['game']));
        }
        ListFilters::sort($query, $filters, 'label');
        $chart = collect(range(6, 0))->map(function ($days): array {
            $date = now()->subDays($days);

            return ['day' => $date->format('D'), 'date' => $date->format('d M'), 'valid' => LicenseEvent::whereIn('action', ['activated', 'validated'])->whereDate('created_at', $date->toDateString())->count(), 'denied' => LicenseEvent::whereIn('action', ['hwid_mismatch', 'expired', 'invalid_key', 'revoked', 'activation_required'])->whereDate('created_at', $date->toDateString())->count()];
        });

        return view('dashboard', [
            'sales' => $request->routeIs('licenses') ? null : app(\App\Services\SalesOverviewService::class)->report(),
            'games' => Game::orderBy('name')->get(),
            'finance' => app(FinanceReportService::class)->report(now('Asia/Jakarta')->startOfMonth()->toDateString(), now('Asia/Jakarta')->toDateString()),
            'licenses' => $query->paginate($filters['per_page'] ?? 8)->withQueryString(),
            'counts' => ['total' => LicenseKey::count(), 'active' => LicenseKey::whereNull('revoked_at')->where('expires_at', '>', now())->whereNotNull('hwid_hash')->count(), 'unbound' => LicenseKey::whereNull('revoked_at')->whereNull('hwid_hash')->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))->count(), 'expired' => LicenseKey::whereNull('revoked_at')->where('expires_at', '<=', now())->count()],
            'events' => LicenseEvent::with('licenseKey')->latest('id')->limit(6)->get(), 'chart' => $chart, 'apiBase' => Setting::apiBaseUrl(), 'isLicenses' => $request->routeIs('licenses'),
        ]);
    }

    public function api(): View
    {
        return view('api', ['apiBase' => Setting::apiBaseUrl(), 'games' => Game::orderBy('name')->get()]);
    }

    public function activity(Request $request): View
    {
        $filters = ListFilters::read($request, ['action' => ['nullable', 'string', 'max:80']]);
        $query = LicenseEvent::with('licenseKey');
        if ($filters['q'] ?? null) {
            $query->where(fn ($q) => $q->where('action', 'like', '%'.$filters['q'].'%')->orWhere('ip_address', 'like', '%'.$filters['q'].'%')->orWhere('metadata', 'like', '%'.$filters['q'].'%')->orWhereHas('licenseKey', fn ($license) => $license->where('label', 'like', '%'.$filters['q'].'%')->orWhere('member_name', 'like', '%'.$filters['q'].'%')));
        }
        if ($filters['action'] ?? null) {
            $query->where('action', $filters['action']);
        }
        ListFilters::dates($query, $filters);
        ListFilters::sort($query, $filters, 'action');

        return view('activity', ['events' => $query->paginate($filters['per_page'] ?? 8)->withQueryString(), 'actions' => LicenseEvent::distinct()->orderBy('action')->pluck('action')]);
    }
}
