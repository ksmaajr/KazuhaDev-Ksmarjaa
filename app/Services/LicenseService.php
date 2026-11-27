<?php

namespace App\Services;

use App\Models\Game;
use App\Models\LicenseEvent;
use App\Models\LicenseKey;
use App\Models\PurchaseOrder;
use App\Models\Setting;
use App\Support\LicenseDuration;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LicenseService
{
    public function issue(array $data, ?int $actorId, ?string $ip): array
    {
        return DB::transaction(function () use ($data, $actorId, $ip): array {
            $plain = 'KF-'.implode('-', str_split(strtoupper(bin2hex(random_bytes(16))), 8));
            $duration = LicenseDuration::seconds($data);
            $license = LicenseKey::create(['label' => $data['label'], 'member_name' => $data['member_name'] ?? $data['label'], 'notes' => $data['notes'] ?? null, 'key_hash' => hash('sha256', $plain), 'key_hint' => 'KF-'.substr($plain, 3, 4).'••••'.substr($plain, -4), 'duration_seconds' => $duration, 'key_value' => $plain, 'expires_at' => null]);
            $license->games()->sync($data['game_ids'] ?? [Game::where('code', 'rules-of-survival')->firstOrFail()->id]);
            $this->record($license, 'created', $ip, null, $actorId, ['game_ids' => $license->games()->allRelatedIds()->all()]);

            return [$license, $plain];
        });
    }

    public function check(string $key, string $hwid, bool $bind, ?string $ip, string $gameCode = 'rules-of-survival'): array
    {
        return DB::transaction(function () use ($key, $hwid, $bind, $ip, $gameCode): array {
            $license = LicenseKey::where('key_hash', hash('sha256', $key))->lockForUpdate()->first();
            $fingerprint = substr(hash('sha256', $hwid), 0, 12);
            if (! $license) {
                return $this->deny(null, 'invalid_key', 401, $ip, $fingerprint);
            }
            if ($license->revoked_at) {
                return $this->deny($license, 'revoked', 403, $ip, $fingerprint);
            }
            $game = Game::where('code', $gameCode)->lockForUpdate()->first();
            if (! $game) {
                return $this->deny($license, 'game_unavailable', 403, $ip, $fingerprint);
            }
            if (! $license->games()->whereKey($game->id)->exists()) {
                return $this->deny($license, 'game_not_allowed', 403, $ip, $fingerprint);
            }
            $hash = hash('sha256', $hwid);
            $newlyBound = false;
            if ($license->hwid_hash === null) {
                if (! $bind) {
                    return $this->deny($license, 'activation_required', 409, $ip, $fingerprint);
                }
                $license->hwid_hash = $hash;
                $license->hwid = $hwid;
                if ($license->activated_at === null) {
                    $license->activated_at = now();
                    $license->expires_at = now()->addSeconds($license->duration_seconds);
                }
                $license->bound_at = now();
                $newlyBound = true;
            } elseif (! hash_equals($license->hwid_hash, $hash)) {
                return $this->deny($license, 'hwid_mismatch', 403, $ip, $fingerprint);
            }
            if ($license->expires_at?->lessThanOrEqualTo(now())) {
                return $this->deny($license, 'expired', 403, $ip, $fingerprint,
                    $license->hwid_hash !== null ? $this->accountData($license, $game, 'expired') : null);
            }
            if ($bind) {
                $license->last_seen_at = now();
                $license->save();
                $this->record($license, $newlyBound ? 'activated' : 'validated', $ip, $fingerprint, null, ['game_code' => $game->code]);
            }

            $purchase = PurchaseOrder::where('status', 'paid')
                ->where(fn ($query) => $query->where('issued_license_id', $license->id)->orWhere('owner_license_id', $license->id))
                ->where(fn ($query) => $query->whereNull('gateway_mode')->orWhere('gateway_mode', 'production'))
                ->orderByDesc('paid_at')->orderByDesc('id')->first();

            return [200, ['success' => true, 'code' => 'valid', 'message' => $newlyBound ? 'HWID terdaftar. Akses diberikan.' : 'Key dan HWID valid.', 'data' => [
                'account' => ['member_name' => $license->member_name ?: $license->label, 'key_hint' => $license->key_hint,
                    'package' => $purchase ? ['name' => $purchase->product_name, 'duration_seconds' => $purchase->duration_seconds ?? $purchase->days * 86400] : null,
                    'games' => $license->games()->get(['games.code', 'games.name'])->map(fn (Game $accessGame): array => ['code' => $accessGame->code, 'name' => $accessGame->name])->all()],
                'game' => ['code' => $game->code, 'name' => $game->name], 'license_id' => $license->id, 'label' => $license->label, 'status' => 'active', 'hwid_bound' => true, 'newly_bound' => $newlyBound,
                'created_at' => $license->created_at->toIso8601String(), 'activated_at' => $license->activated_at?->toIso8601String(),
                'expires_at' => $license->expires_at->toIso8601String(), 'remaining_seconds' => max(0, $license->expires_at->getTimestamp() - now()->getTimestamp()),
                'server_time' => now()->toIso8601String(), 'heartbeat_interval' => (int) Setting::valueFor('heartbeat_interval', '60'),
            ]]];
        }, 3);
    }

    public function reset(LicenseKey $license, ?int $actorId, ?string $ip): void
    {
        DB::transaction(function () use ($license, $actorId, $ip): void {
            $locked = LicenseKey::whereKey($license->id)->lockForUpdate()->firstOrFail();
            $locked->update(['hwid' => null, 'hwid_hash' => null, 'bound_at' => null, 'last_seen_at' => null, 'reset_count' => $locked->reset_count + 1]);
            $this->record($locked, 'hwid_reset', $ip, null, $actorId);
        });
    }

    public function revoke(LicenseKey $license, ?int $actorId, ?string $ip): void
    {
        DB::transaction(function () use ($license, $actorId, $ip): void {
            $locked = LicenseKey::whereKey($license->id)->lockForUpdate()->firstOrFail();
            if (! $locked->revoked_at) {
                $locked->update(['revoked_at' => now()]);
                $this->record($locked, 'revoked', $ip, null, $actorId);
            }
        });
    }

    public function remove(LicenseKey $license, ?int $actorId, ?string $ip): void
    {
        DB::transaction(function () use ($license, $actorId, $ip): void {
            $locked = LicenseKey::whereKey($license->id)->lockForUpdate()->firstOrFail();
            $this->record($locked, 'deleted', $ip, null, $actorId);
            $locked->delete();
        });
    }

    public function extend(LicenseKey $license, int $days, ?int $actorId, ?string $ip): void
    {
        $this->extendSeconds($license, $days * 86400, $actorId, $ip);
    }

    public function extendSeconds(LicenseKey $license, int $seconds, ?int $actorId, ?string $ip): void
    {
        DB::transaction(function () use ($license, $seconds, $actorId, $ip): void {
            $locked = LicenseKey::whereKey($license->id)->lockForUpdate()->firstOrFail();
            if ($locked->revoked_at) {
                throw ValidationException::withMessages(['days' => 'Key yang dicabut tidak dapat diperpanjang.']);
            }

            $update = ['duration_seconds' => $locked->duration_seconds + $seconds];
            if ($locked->activated_at !== null) {
                $base = $locked->expires_at && $locked->expires_at->isFuture() ? $locked->expires_at : now();
                $update['expires_at'] = $base->addSeconds($seconds);
            }
            $locked->update($update);
            $this->record($locked, 'extended', $ip, null, $actorId);
        });
    }

    public function edit(LicenseKey $license, array $data, ?int $actorId, ?string $ip): void
    {
        DB::transaction(function () use ($license, $data, $actorId, $ip): void {
            $locked = LicenseKey::whereKey($license->id)->lockForUpdate()->firstOrFail();
            if (isset($data['days']) || isset($data['hours'])) {
                if ($locked->activated_at !== null || $locked->revoked_at) {
                    throw ValidationException::withMessages([isset($data['hours']) ? 'hours' : 'days' => 'Durasi key yang sudah aktif diubah melalui menu Perpanjang.']);
                }
                $locked->duration_seconds = LicenseDuration::seconds($data);
            }
            $locked->label = $data['label'];
            if (isset($data['member_name'])) {
                $locked->member_name = $data['member_name'];
            }
            $locked->notes = $data['notes'] ?? null;
            $locked->save();
            if (array_key_exists('game_ids', $data)) {
                $locked->games()->sync($data['game_ids']);
            }
            $this->record($locked, 'edited', $ip, null, $actorId, ['game_ids' => $locked->games()->allRelatedIds()->all()]);
        });
    }

    private function accountData(LicenseKey $license, Game $game, string $status): array
    {
        $purchase = PurchaseOrder::where('status', 'paid')
            ->where(fn ($query) => $query->where('issued_license_id', $license->id)->orWhere('owner_license_id', $license->id))
            ->where(fn ($query) => $query->whereNull('gateway_mode')->orWhere('gateway_mode', 'production'))
            ->orderByDesc('paid_at')->orderByDesc('id')->first();

        return [
            'account' => ['member_name' => $license->member_name ?: $license->label, 'key_hint' => $license->key_hint,
                'package' => $purchase ? ['name' => $purchase->product_name, 'duration_seconds' => $purchase->duration_seconds ?? $purchase->days * 86400] : null,
                'games' => $license->games()->get(['games.code', 'games.name'])->map(fn (Game $accessGame): array => ['code' => $accessGame->code, 'name' => $accessGame->name])->all()],
            'game' => ['code' => $game->code, 'name' => $game->name], 'license_id' => $license->id,
            'label' => $license->label, 'status' => $status, 'hwid_bound' => $license->hwid_hash !== null,
            'expires_at' => $license->expires_at?->toIso8601String(),
            'remaining_seconds' => max(0, ($license->expires_at?->getTimestamp() ?? 0) - now()->getTimestamp()),
            'server_time' => now()->toIso8601String(), 'heartbeat_interval' => (int) Setting::valueFor('heartbeat_interval', '60'),
        ];
    }

    private function deny(?LicenseKey $license, string $code, int $status, ?string $ip, string $fingerprint, ?array $data = null): array
    {
        $messages = ['game_unavailable' => 'Kode game tidak dikenal.', 'game_not_allowed' => 'Key tidak memiliki akses ke game ini.', 'invalid_key' => 'Key tidak ditemukan.', 'revoked' => 'Key telah dicabut.', 'expired' => 'Masa berlaku key sudah habis.', 'hwid_mismatch' => 'Key terikat ke perangkat lain.', 'activation_required' => 'Aktivasi melalui POST terlebih dahulu.'];
        $this->record($license, $code, $ip, $fingerprint);

        return [$status, ['success' => false, 'code' => $code, 'message' => $messages[$code], 'data' => $data]];
    }

    private function record(?LicenseKey $license, string $action, ?string $ip, ?string $fingerprint = null, ?int $actorId = null, array $metadata = []): void
    {
        LicenseEvent::create(['license_key_id' => $license?->id, 'user_id' => $actorId, 'action' => $action, 'ip_address' => $ip, 'hwid_fingerprint' => $fingerprint, 'metadata' => $metadata]);
    }
}
