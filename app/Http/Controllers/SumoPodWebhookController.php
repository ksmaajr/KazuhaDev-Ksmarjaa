<?php

namespace App\Http\Controllers;

use App\Models\LicenseEvent;
use App\Models\PurchaseOrder;
use App\Services\PurchaseService;
use App\Support\PaymentSettings;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SumoPodWebhookController extends Controller
{
    public function __invoke(Request $request, string $mode, PurchaseService $service): JsonResponse
    {
        abort_unless(in_array($mode, ['sandbox', 'production']), 404);
        $secret = PaymentSettings::secret('webhook_secret', $mode);
        $token = PaymentSettings::secret('webhook_token', $mode);
        $verified = false;
        if ($secret !== '') {
            $timestamp = $request->header('svix-timestamp', '');
            $id = $request->header('svix-id', '');
            if (ctype_digit($timestamp) && abs(time() - (int) $timestamp) <= 300 && $id !== '') {
                $bytes = base64_decode(str_starts_with($secret, 'whsec_') ? substr($secret, 6) : $secret, true);
                if ($bytes !== false && $bytes !== '') {
                    $expected = base64_encode(hash_hmac('sha256', $id.'.'.$timestamp.'.'.$request->getContent(), $bytes, true));
                    foreach (explode(' ', $request->header('svix-signature', '')) as $signature) {
                        if (str_starts_with($signature, 'v1,') && hash_equals($expected, substr($signature, 3))) {
                            $verified = true;
                        }
                    }
                }
            }
        } elseif ($token !== '') {
            $verified = hash_equals($token, $request->header('X-Webhook-Token', ''));
        }
        abort_unless($verified, 401, 'Invalid webhook verification.');
        $payload = $request->validate(['event_type' => ['required', 'in:payment.completed,payment.failed,payment.expired,payment.test'], 'data' => ['required', 'array']]);
        if ($payload['event_type'] === 'payment.test') {
            return response()->json(['received' => true]);
        }
        $data = $request->validate(['data.payment_id' => ['required', 'string', 'max:100'], 'data.order_id' => ['required', 'string', 'max:40'], 'data.amount' => ['required', 'integer', 'min:1'], 'data.fee' => ['required_if:event_type,payment.completed', 'integer', 'min:0'], 'data.net_amount' => ['required_if:event_type,payment.completed', 'integer', 'min:0'], 'data.paid_at' => ['required_if:event_type,payment.completed', 'date'], 'data.status' => ['required', 'string'], 'data.currency' => ['sometimes', 'in:IDR']])['data'];
        DB::transaction(function () use ($request, $payload, $data, $mode, $service): void {
            $order = PurchaseOrder::where('number', $data['order_id'])->lockForUpdate()->firstOrFail();
            abort_unless($order->gateway_mode === $mode && $order->amount === (int) $data['amount'], 409, 'Payment does not match order.');
            if ($order->gateway_payment_id === null && in_array($order->gateway_status, ['creating', 'uncertain'])) {
                $order->update(['gateway_payment_id' => $data['payment_id']]);
            }
            abort_unless($order->gateway_payment_id === $data['payment_id'], 409, 'Payment identifier mismatch.');
            if ($order->status === 'paid') {
                return;
            }
            if ($payload['event_type'] === 'payment.completed') {
                abort_unless($order->status === 'pending' && $data['status'] === 'completed' && (int) $data['fee'] + (int) $data['net_amount'] === $order->amount, 409, 'Payment totals or state mismatch.');
                abort_if(CarbonImmutable::parse($data['paid_at'])->gt(now()->addMinutes(5)), 422, 'Invalid payment timestamp.');
                if ($mode === 'sandbox') {
                    $license = null;
                    if ($order->purpose !== 'renewal') {
                        $seconds = $order->duration_seconds ?? $order->days * 86400;
                        [$license] = app(\App\Services\LicenseService::class)->issue([
                            'label' => '[DEMO] '.$order->product_name.' · '.$order->number,
                            'member_name' => $order->member_name,
                            'notes' => 'Sandbox: portal member testing only.',
                            'hours' => intdiv($seconds, 3600),
                            'minutes' => intdiv($seconds % 3600, 60),
                            'game_ids' => $order->game_ids ?? [],
                        ], null, $request->ip());
                    }
                    $order->update(['status' => 'paid', 'gateway_status' => 'completed', 'gateway_fee' => (int) $data['fee'], 'paid_at' => CarbonImmutable::parse($data['paid_at'])->utc(), 'verified_at' => now(), 'payment_reference' => 'sandbox:'.$data['payment_id'], 'issued_license_id' => $license?->id, 'payment_notes' => 'Simulasi sandbox; key demo hanya untuk portal member, tanpa perpanjangan produksi atau pendapatan riil.']);
                    LicenseEvent::create(['license_key_id' => $order->owner_license_id, 'action' => 'payment_test_completed', 'metadata' => ['order_number' => $order->number, 'mode' => 'sandbox']]);

                    return;
                }
                $service->settle($order, null, $mode.':'.$data['payment_id'], $request->ip(), ['gateway_verified' => true, 'payment_method' => 'qris', 'payment_provider' => 'SumoPod', 'gateway_paid_at' => $data['paid_at'], 'fee' => (int) $data['fee']]);
                $order->refresh()->update(['gateway_status' => 'completed', 'gateway_fee' => (int) $data['fee']]);
            } else {
                $state = $payload['event_type'] === 'payment.expired' ? 'expired' : 'failed';
                $order->update(['gateway_status' => $state]);
                LicenseEvent::create(['license_key_id' => $order->owner_license_id, 'action' => 'payment_'.$state, 'ip_address' => $request->ip(), 'metadata' => ['order_number' => $order->number]]);
            }
        }, 3);

        return response()->json(['received' => true]);
    }
}
