<?php

namespace App\Services;

use App\Models\LicenseEvent;
use App\Models\PurchaseOrder;
use App\Support\PaymentSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
        $startedAt = hrtime(true);
        $stage = 'request';
        $diagnostics = ['order_number' => $order->number, 'gateway_mode' => $mode, 'method' => $method];
        try {
            $response = Http::acceptJson()->asJson()->withoutRedirecting()->withHeaders(['X-Api-Key' => PaymentSettings::secret('api_key', $mode)])->connectTimeout(4)->timeout(8)->post($mode === 'sandbox' ? 'https://api-pay-sandbox.sumopod.com/api/v1/payments' : 'https://api-pay.sumopod.com/api/v1/payments', ['order_id' => $order->number, 'amount' => $order->amount, 'currency' => 'IDR', 'expires_in_hours' => 24, 'payment_method_type_code' => $method, 'success_return_url' => route('checkout.receipt', ['order' => $order->number]), 'cancel_return_url' => route('checkout.receipt', ['order' => $order->number])]);
            $stage = 'http_response';
            $diagnostics['http_status'] = $response->status();
            $payload = $response->json();
            $diagnostics['response_is_object'] = is_array($payload);
            $diagnostics['response_fields'] = is_array($payload) ? array_values(array_intersect(['data', 'payment_id', 'order_id', 'amount', 'payment_link_url', 'fee', 'expires_at', 'error', 'message', 'status', 'success'], array_keys($payload))) : [];
            $diagnostics['nested_fields'] = is_array($payload['data'] ?? null) ? array_values(array_intersect(['payment_id', 'order_id', 'amount', 'payment_link_url', 'fee', 'expires_at'], array_keys($payload['data']))) : [];
            if (! $response->successful()) {
                throw new \RuntimeException('Gateway rejected request.');
            }
            $stage = 'validate_response';
            $data = $payload;
            if (is_array($payload['data'] ?? null) && ! isset($payload['payment_id'])) {
                $data = $payload['data'];
            }
            $url = is_array($data) && is_string($data['payment_link_url'] ?? null) ? trim($data['payment_link_url']) : '';
            if (str_starts_with($url, '//')) {
                $url = 'https:'.$url;
            }
            $parts = parse_url($url);
            $host = strtolower($parts['host'] ?? '');
            $scheme = strtolower($parts['scheme'] ?? '');
            $diagnostics['payment_url_host'] = preg_match('/^[a-z0-9.-]{1,253}$/D', $host) ? $host : null;
            $diagnostics['payment_url_scheme'] = in_array($scheme, ['http', 'https'], true) ? $scheme : null;
            $diagnostics['payment_url_present'] = $url !== '';
            $diagnostics['payment_url_relative'] = $url !== '' && $host === '';
            $diagnostics['payment_id_present'] = is_array($data) && ! empty($data['payment_id']);
            $diagnostics['order_matches'] = is_array($data) && ($data['order_id'] ?? null) === $order->number;
            $diagnostics['amount_matches'] = is_array($data) && is_numeric($data['amount'] ?? null) && (float) $data['amount'] === (float) $order->amount;
            $diagnostics['payment_url_valid'] = $scheme === 'https' && ($host === 'sumopod.com' || str_ends_with($host, '.sumopod.com')) && filter_var($url, FILTER_VALIDATE_URL) !== false && ! isset($parts['user']) && ! isset($parts['pass']) && (! isset($parts['port']) || $parts['port'] === 443) && ! str_contains($url, '\\');
            $diagnostics['fee_present'] = isset($data['fee']);
            $diagnostics['expires_at_present'] = isset($data['expires_at']);
            if (! is_array($data) || ! $diagnostics['payment_id_present'] || ! is_scalar($data['payment_id']) || ! $diagnostics['order_matches'] || ! $diagnostics['amount_matches'] || ! $diagnostics['payment_url_valid']) {
                throw new \RuntimeException('Invalid gateway response.');
            }
            $data['payment_link_url'] = $url;
            $stage = 'persist_invoice';
            DB::transaction(function () use ($order, $data): void {
                $locked = PurchaseOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
                $locked->update(['gateway_payment_id' => (string) $data['payment_id'], 'gateway_payment_url' => $data['payment_link_url'], 'gateway_status' => $locked->status === 'paid' ? 'completed' : 'pending', 'gateway_fee' => isset($data['fee']) ? (int) $data['fee'] : null, 'gateway_expires_at' => $data['expires_at'] ?? null]);
                LicenseEvent::create(['license_key_id' => $locked->owner_license_id, 'action' => 'payment_link_created', 'metadata' => ['order_number' => $locked->number, 'mode' => $locked->gateway_mode]]);
            }, 3);

            Log::info('sumopod.invoice.created', $diagnostics + ['elapsed_ms' => (int) ((hrtime(true) - $startedAt) / 1000000)]);

            return $order->fresh();
        } catch (\Throwable $exception) {
            Log::warning('sumopod.invoice.uncertain', $diagnostics + [
                'stage' => $stage,
                'exception_type' => $exception::class,
                'elapsed_ms' => (int) ((hrtime(true) - $startedAt) / 1000000),
            ]);
            PurchaseOrder::whereKey($order->id)->where('gateway_status', 'creating')->update(['gateway_status' => 'uncertain']);
            throw ValidationException::withMessages(['payment' => 'Respons gateway belum dapat dipastikan. Pesanan tetap tersimpan; jangan buat pembayaran ulang. Hubungi admin dengan nomor pesanan untuk rekonsiliasi.']);
        }
    }
}
