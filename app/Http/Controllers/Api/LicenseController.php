<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateLicenseRequest;
use App\Models\Game;
use App\Models\LicenseEvent;
use App\Services\LicenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LicenseController extends Controller
{
    public function validateKey(ValidateLicenseRequest $request, LicenseService $service): JsonResponse
    {
        [$status,$result] = $service->check($request->validated('key'), $request->validated('hwid'), true, $request->ip(), $request->validated('game_code'));

        return response()->json($result, $status)->header('Cache-Control', 'no-store');
    }

    public function dll(Request $request, LicenseService $service): JsonResponse|BinaryFileResponse
    {
        $input = validator(['key' => $request->bearerToken(), 'hwid' => $request->header('X-HWID'), 'game_code' => $request->header('X-Game')], (new ValidateLicenseRequest)->rules())->validate();

        return DB::transaction(function () use ($input, $request, $service) {
            [$status,$result] = $service->check($input['key'], $input['hwid'], false, $request->ip(), $input['game_code']);
            if ($status !== 200) {
                return response()->json($result, $status)->header('Cache-Control', 'no-store');
            }
            $game = Game::where('code', $input['game_code'])->firstOrFail();
            if (! $game->dll_path || ! Storage::disk('local')->exists($game->dll_path)) {
                return response()->json(['success' => false, 'code' => 'release_unavailable', 'message' => 'DLL belum tersedia.', 'data' => null], 404);
            }
            LicenseEvent::create(['license_key_id' => $result['data']['license_id'], 'action' => 'downloaded_dll', 'ip_address' => $request->ip(), 'metadata' => ['game_code' => $game->code, 'game_name' => $game->name, 'sha256' => $game->dll_sha256, 'channel' => 'injector_api']]);

            return response()->download(Storage::disk('local')->path($game->dll_path), $game->dll_name, ['Content-Type' => 'application/octet-stream', 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store', 'X-Checksum-SHA256' => $game->dll_sha256 ?? '']);
        });
    }

    public function status(Request $request, LicenseService $service): JsonResponse
    {
        $input = validator(['key' => $request->bearerToken(), 'hwid' => $request->header('X-HWID'), 'game_code' => $request->header('X-Game')], (new ValidateLicenseRequest)->rules())->validate();
        [$status,$result] = $service->check($input['key'], $input['hwid'], false, $request->ip(), $input['game_code']);

        return response()->json($result, $status)->header('Cache-Control', 'no-store');
    }
}
