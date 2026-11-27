<?php

namespace App\Services;

use App\Models\LicenseEvent;
use App\Models\PurchaseOrder;
use App\Support\PaymentSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class SumoPodService
{
    public function createLink(PurchaseOrder $order, string $method = 'QRIS'): PurchaseOrder
    {
        $mode = PaymentSettings::mode();
        if (! PaymentSettings::ready($mode)) {
            throw ValidationException::withMessages(['payment' => PaymentSettings::unavailableReason($mode)]);
        }
        if ($mode === 'production' && $method !== 'QRIS') {
            throw ValidationException::withMessages(['payment' => 'Metode ini belum tersedia di production.']);
        }
        $claimed = DB::transaction(function () use ($order, $mode): bool {
            $locked = PurchaseOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'pending' || $locked->gateway_payment_id) {
                return false;
            }
            if ($locked->gateway_status) {
                throw ValidationException::withMessages(['payment' => 'Permintaan pembayaran sebelumnya sudah dikirim. Jangan bayar ulang; tunggu rekonsiliasi atau hubungi admin dengan nomor pesanan.']);
            }
            $locked->update(['gateway_mode' => $mode, 'gateway_status' => 'creating', 'payment_provider' => 'SumoPod', 'payment_method' => 'qris']);

            return true;
        }, 3);
        if (! $claimed) {
            return $order->fresh();
        }
        try {
            $response = Http::acceptJson()->asJson()->withoutRedirecting()->withHeaders(['X-Api-Key' => PaymentSettings::secret('api_key', $mode)])->connectTimeout(4)->timeout(8)->post($mode === 'sandbox' ? 'https://api-pay-sandbox.sumopod.com/api/v1/payments' : 'https://api-pay.sumopod.com/api/v1/payments', ['order_id' => $order->number, 'amount' => $order->amount, 'currency' => 'IDR', 'expires_in_hours' => 24, 'payment_method_type_code' => $method, 'success_return_url' => route('checkout.receipt', ['order' => $order->number]), 'cancel_return_url' => route('checkout.receipt', ['order' => $order->number])]);
            if (! $response->successful()) {
                throw new \RuntimeException('Gateway rejected request.');
            }
            $data = $response->json();
            $host = parse_url($data['payment_link_url'] ?? '', PHP_URL_HOST);
            if (! is_array($data) || empty($data['payment_id']) || ($data['order_id'] ?? null) !== $order->number || (int) ($data['amount'] ?? -1) !== $order->amount || ! is_string($host) || ! str_ends_with($host, '.sumopod.com') || parse_url($data['payment_link_url'], PHP_URL_SCHEME) !== 'https') {
                throw new \RuntimeException('Invalid gateway response.');
            }
            DB::transaction(function () use ($order, $data): void {
                $locked = PurchaseOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
                $locked->update(['gateway_payment_id' => (string) $data['payment_id'], 'gateway_payment_url' => $data['payment_link_url'], 'gateway_status' => $locked->status === 'paid' ? 'completed' : 'pending', 'gateway_fee' => isset($data['fee']) ? (int) $data['fee'] : null, 'gateway_expires_at' => $data['expires_at'] ?? null]);
                LicenseEvent::create(['license_key_id' => $locked->owner_license_id, 'action' => 'payment_link_created', 'metadata' => ['order_number' => $locked->number, 'mode' => $locked->gateway_mode]]);
            }, 3);

            return $order->fresh();
        } catch (\Throwable $exception) {
            PurchaseOrder::whereKey($order->id)->where('gateway_status', 'creating')->update(['gateway_status' => 'uncertain']);
            throw ValidationException::withMessages(['payment' => 'Respons gateway belum dapat dipastikan. Pesanan tetap tersimpan; jangan buat pembayaran ulang. Hubungi admin dengan nomor pesanan untuk rekonsiliasi.']);
        }
    }
}
