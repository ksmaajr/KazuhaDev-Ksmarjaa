<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGameRequest;
use App\Http\Requests\UpdateGameRequest;
use App\Models\Game;
use App\Models\LicenseEvent;
use App\Models\LicenseKey;
use App\Services\GameReleaseService;
use App\Support\ListFilters;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GameController extends Controller
{
    public function index(Request $request): View
    {
        $filters = ListFilters::read($request);
        $query = Game::withCount('licenses');
        ListFilters::search($query, $filters, ['name', 'code', 'release_version']);
        ListFilters::dates($query, $filters);
        ListFilters::sort($query, $filters);

        return view('games', ['games' => $query->paginate($filters['per_page'] ?? 10)->withQueryString()]);
    }

    public function store(StoreGameRequest $request, GameReleaseService $service): RedirectResponse
    {
        $service->save(null, $request->validated(), $request->user()->id, $request->ip());

        return redirect()->route('games.index')->with('success', 'Game ditambahkan. Pilih game ini saat membuat atau mengedit key.');
    }

    public function update(UpdateGameRequest $request, Game $game, GameReleaseService $service): RedirectResponse
    {
        $service->save($game, $request->validated(), $request->user()->id, $request->ip());

        return redirect()->route('games.index')->with('success', 'Konfigurasi dan berkas game diperbarui.');
    }

    public function artwork(Game $game, string $kind): BinaryFileResponse
    {
        abort_unless(in_array($kind, ['banner', 'logo'], true), 404);
        $path = $game->{$kind.'_path'};
        if (! $path && $game->code === 'rules-of-survival') {
            return response()->file(public_path('images/games/rules-of-survival-'.$kind.($kind === 'banner' ? '.jpg' : '.png')), ['Cache-Control' => 'public, max-age=86400', 'X-Content-Type-Options' => 'nosniff']);
        }
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path), ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, max-age=300']);
    }

    public function download(Request $request, Game $game, string $kind): BinaryFileResponse
    {
        abort_unless(in_array($kind, ['injector', 'dll'], true), 404);

        return DB::transaction(function () use ($request, $game, $kind): BinaryFileResponse {
            $license = null;
            if ($request->routeIs('member.*')) {
                $license = LicenseKey::whereKey($request->attributes->get('member_license')->id)->lockForUpdate()->firstOrFail();
                abort_if($license->revoked_at || $license->expires_at?->lessThanOrEqualTo(now()), 403);
            }
            $current = Game::whereKey($game->id)->lockForUpdate()->firstOrFail();
            if ($license) {
                abort_unless($license->games()->whereKey($current->id)->exists(), 403);
            }
            $path = $current->{$kind.'_path'};
            abort_unless($path && Storage::disk('local')->exists($path), 404);
            LicenseEvent::create(['license_key_id' => $license?->id, 'user_id' => $license ? null : $request->user()->id, 'action' => 'downloaded_'.$kind, 'ip_address' => $request->ip(), 'metadata' => ['game_id' => $current->id, 'game_name' => $current->name, 'game_code' => $current->code, 'sha256' => $current->{$kind.'_sha256'}]]);

            return response()->download(Storage::disk('local')->path($path), $current->{$kind.'_name'}, ['Content-Type' => 'application/octet-stream', 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store']);
        });
    }
}
