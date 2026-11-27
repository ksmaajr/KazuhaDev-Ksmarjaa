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
        $version = validator(['version' => $request->header('X-Game-Version')], ['version' => ['nullable', 'string', 'max:50', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/']])->validate()['version'] ?? null;

        return DB::transaction(function () use ($input, $request, $service, $version) {
            [$status,$result] = $service->check($input['key'], $input['hwid'], false, $request->ip(), $input['game_code']);
            if ($status !== 200) {
                return response()->json($result, $status)->header('Cache-Control', 'no-store');
            }
            $game = Game::where('code', $input['game_code'])->firstOrFail();
            $variants = $game->dll_variants ?? [];
            if ($version === null && count($variants) > 0) {
                return response()->json(['success' => false, 'code' => 'game_version_required', 'message' => 'Kirim kode versi melalui X-Game-Version.', 'data' => null], 422)->header('Cache-Control', 'no-store');
            }
            $release = $version !== null ? ($variants[$version] ?? null) : ['path' => $game->dll_path, 'filename' => $game->dll_name, 'sha256' => $game->dll_sha256];
            if (! $release || ! $release['path'] || ! Storage::disk('local')->exists($release['path'])) {
                return response()->json(['success' => false, 'code' => 'release_unavailable', 'message' => 'DLL belum tersedia.', 'data' => null], 404);
            }
            LicenseEvent::create(['license_key_id' => $result['data']['license_id'], 'action' => 'downloaded_dll', 'ip_address' => $request->ip(), 'metadata' => ['game_code' => $game->code, 'game_name' => $game->name, 'game_version' => $version, 'sha256' => $release['sha256'], 'channel' => 'injector_api']]);

            $headers = ['Content-Type' => 'application/octet-stream', 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store', 'X-Checksum-SHA256' => $release['sha256'] ?? ''];
            if ($version !== null) {
                $headers['X-Game-Version'] = $version;
            }

            return response()->download(Storage::disk('local')->path($release['path']), $release['filename'], $headers);
        });
    }

    public function status(Request $request, LicenseService $service): JsonResponse
    {
        $input = validator(['key' => $request->bearerToken(), 'hwid' => $request->header('X-HWID'), 'game_code' => $request->header('X-Game')], (new ValidateLicenseRequest)->rules())->validate();
        [$status,$result] = $service->check($input['key'], $input['hwid'], false, $request->ip(), $input['game_code']);

        return response()->json($result, $status)->header('Cache-Control', 'no-store');
    }
}
