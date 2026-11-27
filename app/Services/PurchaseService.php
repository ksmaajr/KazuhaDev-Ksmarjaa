<?php

namespace App\Services;

use App\Models\FinancialTransaction;
use App\Models\Game;
use App\Models\LicenseEvent;
use App\Models\LicenseKey;
use App\Models\LicenseProduct;
use App\Models\PurchaseOrder;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    public function order(LicenseKey $owner, int $productId, string $token, ?string $ip, bool $renewal = false): PurchaseOrder
    {
        return DB::transaction(function () use ($owner, $productId, $token, $ip, $renewal): PurchaseOrder {
            $locked = LicenseKey::whereKey($owner->id)->lockForUpdate()->firstOrFail();
            if ($locked->revoked_at) {
                throw ValidationException::withMessages(['order' => 'Key Anda sudah dicabut.']);
            }
            $existing = PurchaseOrder::where('idempotency_key', $token)->first();
            if ($existing) {
                abort_unless($existing->owner_license_id === $locked->id && $existing->purpose === ($renewal ? 'renewal' : 'purchase'), 403);

                return $existing;
            }
            if ($pending = PurchaseOrder::where('owner_license_id', $locked->id)->where('license_product_id', $productId)->where('purpose', $renewal ? 'renewal' : 'purchase')->where('status', 'pending')->lockForUpdate()->first()) {
                return $pending;
            }
            $product = LicenseProduct::whereKey($productId)->lockForUpdate()->firstOrFail();
            if (! $product->is_active) {
                throw ValidationException::withMessages(['product' => 'Paket tidak tersedia.']);
            }
            // Renewal purchases add time only; the existing game entitlements remain unchanged.
            $gameIds = $renewal ? $locked->games()->allRelatedIds()->all() : $product->game_ids;
            $order = PurchaseOrder::create(['number' => 'KZ-'.now()->format('ymd').'-'.strtoupper(Str::random(8)), 'idempotency_key' => $token, 'owner_license_id' => $locked->id, 'license_product_id' => $product->id, 'member_name' => $locked->member_name ?: $locked->label, 'product_name' => $product->name, 'days' => $product->days, 'duration_seconds' => $product->duration_seconds ?? $product->days * 86400, 'product_description' => $product->description, 'game_snapshot' => Game::whereIn('id', $gameIds)->get(['id', 'name', 'code'])->toArray(), 'game_ids' => $gameIds, 'amount' => $product->price]);
            $order->update(['purpose' => $renewal ? 'renewal' : 'purchase', 'target_license_id' => $renewal ? $locked->id : null, 'target_key_hint' => $locked->key_hint]);
            LicenseEvent::create(['license_key_id' => $locked->id, 'action' => 'order_created', 'ip_address' => $ip, 'metadata' => ['order_number' => $order->number, 'amount' => $order->amount]]);

            return $order;
        }, 3);
    }

    public function guestOrder(array $buyer, int $productId, string $token, string $guestHash, ?string $ip): PurchaseOrder
    {
        return DB::transaction(function () use ($buyer, $productId, $token, $guestHash, $ip): PurchaseOrder {
            $product = LicenseProduct::whereKey($productId)->where('is_active', true)->lockForUpdate()->firstOrFail();
            if ($existing = PurchaseOrder::where('idempotency_key', $token)->first()) {
                abort_unless($existing->guest_token_hash && hash_equals($existing->guest_token_hash, $guestHash), 403);

                return $existing;
            }
            if ($pending = PurchaseOrder::where('guest_token_hash', $guestHash)->where('license_product_id', $productId)->where('status', 'pending')->lockForUpdate()->first()) {
                return $pending;
            }
            $order = PurchaseOrder::create(['number' => 'KZ-'.now()->format('ymd').'-'.strtoupper(Str::random(8)), 'idempotency_key' => $token, 'owner_license_id' => null, 'guest_token_hash' => $guestHash, 'license_product_id' => $product->id, 'member_name' => $buyer['member_name'], 'customer_contact' => $buyer['customer_contact'], 'customer_email' => $buyer['customer_email'] ?? null, 'product_name' => $product->name, 'product_description' => $product->description, 'days' => $product->days, 'duration_seconds' => $product->duration_seconds ?? $product->days * 86400, 'game_ids' => $product->game_ids, 'game_snapshot' => Game::whereIn('id', $product->game_ids)->get(['id', 'name', 'code'])->toArray(), 'amount' => $product->price]);
            LicenseEvent::create(['action' => 'order_created', 'ip_address' => $ip, 'metadata' => ['order_number' => $order->number, 'amount' => $order->amount]]);

            return $order;
        }, 3);
    }

    public function settle(PurchaseOrder $order, ?int $actor, string $reference, ?string $ip, array $payment = []): PurchaseOrder
    {
        return DB::transaction(function () use ($order, $actor, $reference, $ip, $payment): PurchaseOrder {
            $locked = PurchaseOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($locked->status === 'paid') {
                return $locked;
            }
            if ($locked->status !== 'pending') {
                throw ValidationException::withMessages(['order' => 'Pesanan ini tidak menunggu pembayaran.']);
            }
            if (FinancialTransaction::where('source', ! empty($payment['gateway_verified']) ? 'gateway' : 'manual')->where('external_reference', $reference)->exists()) {
                throw ValidationException::withMessages(['payment_reference' => 'Referensi pembayaran sudah dipakai.']);
            }
            if (($locked->gateway_payment_id || $locked->gateway_status) && empty($payment['gateway_verified'])) {
                throw ValidationException::withMessages(['payment_reference' => 'Lewati verifikasi manual: pembayaran ini menunggu webhook gateway terverifikasi.']);
            }
            if ($locked->purpose === 'renewal') {
                $license = LicenseKey::findOrFail($locked->target_license_id);
                abort_unless($license->id === $locked->owner_license_id, 409);
                app(LicenseService::class)->extendSeconds($license, $locked->duration_seconds ?? $locked->days * 86400, $actor, $ip);
            } else {
                [$license] = app(LicenseService::class)->issue(['label' => $locked->product_name.' · '.$locked->number, 'member_name' => $locked->member_name, 'hours' => intdiv($locked->duration_seconds ?? $locked->days * 86400, 3600), 'minutes' => intdiv(($locked->duration_seconds ?? $locked->days * 86400) % 3600, 60), 'game_ids' => $locked->game_ids], $actor, $ip);
            }
            $paidAt = isset($payment['gateway_paid_at']) ? CarbonImmutable::parse($payment['gateway_paid_at'])->utc() : (isset($payment['paid_at']) ? CarbonImmutable::createFromFormat('Y-m-d\TH:i', $payment['paid_at'], 'Asia/Jakarta')->utc() : now());
            FinancialTransaction::create(['reference' => $locked->number, 'order_id' => $locked->id, 'created_by' => $actor, 'direction' => 'income', 'source' => ! empty($payment['gateway_verified']) ? 'gateway' : 'manual', 'external_reference' => $reference, 'amount' => $locked->amount, 'fee' => (int) ($payment['fee'] ?? 0), 'description' => ($locked->purpose === 'renewal' ? 'Perpanjangan ' : 'Pembelian ').$locked->product_name, 'paid_at' => $paidAt]);
            $locked->update(['status' => 'paid', 'paid_at' => $paidAt, 'payment_reference' => $reference, 'issued_license_id' => $license->id, 'payment_method' => $payment['payment_method'] ?? 'manual', 'payment_provider' => $payment['payment_provider'] ?? null, 'payer_name' => $payment['payer_name'] ?? null, 'payment_notes' => $payment['payment_notes'] ?? null, 'verified_by' => $actor, 'verified_at' => now()]);
            LicenseEvent::create(['license_key_id' => $locked->owner_license_id, 'user_id' => $actor, 'action' => 'order_paid', 'ip_address' => $ip, 'metadata' => ['order_number' => $locked->number, 'amount' => $locked->amount, 'payment_method' => $locked->payment_method, 'payment_reference' => $reference]]);

            return $locked;
        }, 3);
    }

    public function deleteUnpaid(PurchaseOrder $order, ?int $actor, ?string $ip, ?CarbonImmutable $olderThan = null): bool
    {
        return DB::transaction(function () use ($order, $actor, $ip, $olderThan): bool {
            $locked = PurchaseOrder::whereKey($order->id)->lockForUpdate()->first();
            if (! $locked || ($olderThan && $locked->created_at->gt($olderThan))) {
                return false;
            }
            if (! $locked->canBeDeleted() || FinancialTransaction::where('order_id', $locked->id)->exists()) {
                if ($olderThan) {
                    return false;
                }
                throw ValidationException::withMessages(['order' => 'Invoice tidak dapat dihapus: pembayaran sudah diproses atau masih menunggu konfirmasi gateway.']);
            }
            if ($olderThan === null) {
                LicenseEvent::create(['license_key_id' => $locked->owner_license_id, 'user_id' => $actor, 'action' => 'order_deleted', 'ip_address' => $ip, 'metadata' => ['order_number' => $locked->number]]);
            }
            $locked->delete();

            return true;
        }, 3);
    }

    public function cancel(PurchaseOrder $order, ?int $actor, ?string $ip, ?string $reason = null): void
    {
        DB::transaction(function () use ($order, $actor, $ip, $reason): void {
            $locked = PurchaseOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($locked->status === 'cancelled') {
                return;
            }
            if ($locked->status !== 'pending') {
                throw ValidationException::withMessages(['order' => 'Pesanan lunas tidak dapat dibatalkan melalui menu ini.']);
            }
            if ($locked->gateway_payment_id || in_array($locked->gateway_status, ['creating', 'uncertain'])) {
                throw ValidationException::withMessages(['order' => 'Invoice gateway masih harus direkonsiliasi. Tunggu status pembayaran atau kedaluwarsa dari gateway.']);
            }
            $locked->update(['status' => 'cancelled', 'cancelled_at' => now(), 'cancelled_by' => $actor, 'cancellation_reason' => $reason]);
            LicenseEvent::create(['license_key_id' => $locked->owner_license_id, 'user_id' => $actor, 'action' => 'order_cancelled', 'ip_address' => $ip, 'metadata' => ['order_number' => $locked->number]]);
        });
    }
}
