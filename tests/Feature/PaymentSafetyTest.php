<?php

namespace Tests\Feature;

use App\Models\FinancialTransaction;
use App\Models\LicenseKey;
use App\Models\LicenseProduct;
use App\Models\PurchaseOrder;
use App\Models\Setting;
use App\Models\User;
use App\Services\LicenseService;
use App\Services\PurchaseService;
use App\Services\SumoPodService;
use App\Support\PaymentSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PaymentSafetyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    private function configure(string $mode = 'production'): void
    {
        Setting::updateOrCreate(['name' => 'payment_mode'], ['value' => $mode]);
        Setting::updateOrCreate(['name' => 'payment_enabled'], ['value' => '1']);
        PaymentSettings::saveSecret('api_key', 'unit-test-api-key', $mode);
        PaymentSettings::saveSecret('webhook_secret', 'whsec_'.base64_encode('unit-test-signing-secret'), $mode);
    }

    private function order(string $mode = 'production', array $extra = []): PurchaseOrder
    {
        $this->configure($mode);

        return PurchaseOrder::factory()->create(array_merge(['gateway_mode' => $mode, 'gateway_payment_id' => 'payment-'.Str::uuid(), 'gateway_status' => 'pending', 'payment_provider' => 'SumoPod', 'payment_method' => 'qris', 'duration_seconds' => 9300], $extra));
    }

    private function event(PurchaseOrder $order, string $type = 'payment.completed', array $overrides = []): array
    {
        return ['event_type' => $type, 'data' => array_replace(['payment_id' => $order->gateway_payment_id, 'order_id' => $order->number, 'amount' => $order->amount, 'fee' => 650, 'net_amount' => $order->amount - 650, 'status' => 'completed', 'payment_method' => 'qris', 'paid_at' => now()->toIso8601String()], $overrides)];
    }

    private function webhook(array $payload, string $mode = 'production', ?int $timestamp = null, bool $valid = true): TestResponse
    {
        $body = json_encode($payload);
        $time = (string) ($timestamp ?? time());
        $id = 'msg_test_001';
        $signature = base64_encode(hash_hmac('sha256', $id.'.'.$time.'.'.$body, 'unit-test-signing-secret', true));

        return $this->call('POST', '/api/payments/sumopod/'.$mode, [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json', 'HTTP_SVIX_ID' => $id, 'HTTP_SVIX_TIMESTAMP' => $time, 'HTTP_SVIX_SIGNATURE' => 'v1,'.($valid ? $signature : 'invalid')], $body);
    }

    public function test_duplicate_signed_completion_creates_exactly_one_key_and_one_ledger_entry(): void
    {
        $order = $this->order();
        $keys = LicenseKey::count();
        $payload = $this->event($order);
        $this->webhook($payload)->assertOk();
        $this->webhook($payload)->assertOk();
        $this->webhook($payload)->assertOk();
        $order->refresh();
        $this->assertSame('paid', $order->status);
        $this->assertDatabaseCount('license_keys', $keys + 1);
        $this->assertDatabaseCount('financial_transactions', 1);
        $this->assertSame(9300, $order->issuedLicense->duration_seconds);
        $ledger = FinancialTransaction::firstOrFail();
        $this->assertSame(650, $ledger->fee);
        $this->assertSame($order->amount, $ledger->amount);
        $this->assertSame('gateway', $ledger->source);
        $this->assertNull($order->issuedLicense->activated_at);
    }

    public function test_fake_stale_or_wrong_mode_webhook_is_rejected_without_fulfillment(): void
    {
        $order = $this->order();
        $keys = LicenseKey::count();
        $this->webhook($this->event($order), valid: false)->assertUnauthorized();
        $this->webhook($this->event($order), timestamp: time() - 600)->assertUnauthorized();
        $this->webhook($this->event($order), 'sandbox')->assertUnauthorized();
        $this->assertSame('pending', $order->fresh()->status);
        $this->assertDatabaseCount('license_keys', $keys);
        $this->assertDatabaseCount('financial_transactions', 0);
    }

    public function test_wrong_amount_payment_identifier_or_fee_totals_cannot_mark_paid(): void
    {
        $order = $this->order();
        foreach ([['amount' => $order->amount + 1], ['payment_id' => 'foreign-payment'], ['net_amount' => $order->amount], ['status' => 'pending']] as $override) {
            $this->webhook($this->event($order, overrides: $override))->assertStatus(409);
        }
        $this->assertSame('pending', $order->fresh()->status);
        $this->assertDatabaseCount('financial_transactions', 0);
    }

    public function test_failed_expired_and_out_of_order_callbacks_do_not_duplicate_or_reverse_payment(): void
    {
        $order = $this->order();
        $keys = LicenseKey::count();
        $this->webhook($this->event($order, 'payment.failed', ['status' => 'failed']))->assertOk();
        $this->webhook($this->event($order, 'payment.expired', ['status' => 'expired']))->assertOk();
        $this->assertDatabaseCount('license_keys', $keys);
        $this->assertDatabaseCount('financial_transactions', 0);
        $this->webhook($this->event($order))->assertOk();
        $this->webhook($this->event($order, 'payment.expired', ['status' => 'expired']))->assertOk();
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame('completed', $order->fresh()->gateway_status);
        $this->assertDatabaseCount('financial_transactions', 1);
    }

    public function test_sandbox_success_cannot_issue_keys_extend_or_count_as_real_revenue(): void
    {
        $order = $this->order('sandbox');
        $keys = LicenseKey::count();
        $this->webhook($this->event($order), 'sandbox')->assertOk();
        $this->webhook($this->event($order), 'sandbox')->assertOk();
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertNull($order->fresh()->issued_license_id);
        $this->assertDatabaseCount('license_keys', $keys);
        $this->assertDatabaseCount('financial_transactions', 0);
    }

    public function test_invoice_creation_is_once_and_uses_server_price_and_method(): void
    {
        $this->configure();
        $order = PurchaseOrder::factory()->create();
        Http::fake(['api-pay.sumopod.com/*' => Http::response(['payment_id' => 'invoice-once', 'order_id' => $order->number, 'amount' => $order->amount, 'fee' => 650, 'payment_link_url' => 'https://pay.sumopod.com/pay/invoice-once', 'expires_at' => now()->addDay()->toIso8601String()])]);
        $service = app(SumoPodService::class);
        $service->createLink($order);
        $service->createLink($order);
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request['amount'] === $order->amount && $request['order_id'] === $order->number && $request['payment_method_type_code'] === 'QRIS');
        $this->assertSame('invoice-once', $order->fresh()->gateway_payment_id);
    }

    public function test_timeout_blocks_new_invoice_and_later_valid_callback_can_reconcile(): void
    {
        $this->configure();
        $order = PurchaseOrder::factory()->create();
        Http::fake(fn () => Http::failedConnection());
        $service = app(SumoPodService::class);
        for ($attempt = 0; $attempt < 2; $attempt++) {
            try {
                $service->createLink($order);
                $this->fail('Expected guarded failure.');
            } catch (ValidationException) {
            }
        }
        Http::assertSentCount(1);
        $this->assertSame('uncertain', $order->fresh()->gateway_status);
        $order->refresh();
        $payload = $this->event($order, overrides: ['payment_id' => 'reconciled-payment']);
        $this->webhook($payload)->assertOk();
        $this->webhook($payload)->assertOk();
        $this->assertDatabaseCount('financial_transactions', 1);
    }

    public function test_changed_tokens_and_duplicate_submits_reuse_pending_guest_or_member_order(): void
    {
        $product = LicenseProduct::factory()->create();
        [$owner] = app(LicenseService::class)->issue(['label' => 'Buyer', 'days' => 1], null, null);
        $service = app(PurchaseService::class);
        $first = $service->order($owner, $product->id, (string) Str::uuid(), null);
        $second = $service->order($owner, $product->id, (string) Str::uuid(), null);
        $this->assertSame($first->id, $second->id);
        $buyer = ['member_name' => 'Guest', 'customer_contact' => 'Test contact'];
        $a = $service->guestOrder($buyer, $product->id, (string) Str::uuid(), hash('sha256', 'guest'), null);
        $b = $service->guestOrder($buyer, $product->id, (string) Str::uuid(), hash('sha256', 'guest'), null);
        $this->assertSame($a->id, $b->id);
        $this->assertDatabaseCount('purchase_orders', 2);
    }

    public function test_renewal_is_applied_once_to_same_key_and_keeps_hwid_and_activation(): void
    {
        $this->freezeTime();
        [$owner,$key] = app(LicenseService::class)->issue(['label' => 'Renew owner', 'days' => 1], null, null);
        app(LicenseService::class)->check($key, 'RENEWAL-DEVICE-001', true, null);
        $owner->refresh();
        $old = $owner->expires_at;
        $activation = $owner->activated_at;
        $product = LicenseProduct::factory()->create(['game_ids' => $owner->games->modelKeys(), 'duration_seconds' => 5400]);
        $order = app(PurchaseService::class)->order($owner, $product->id, (string) Str::uuid(), null, true);
        $this->configure();
        $order->update(['gateway_mode' => 'production', 'gateway_payment_id' => 'renewal-payment', 'gateway_status' => 'pending']);
        $keys = LicenseKey::count();
        $this->webhook($this->event($order))->assertOk();
        $this->webhook($this->event($order))->assertOk();
        $owner->refresh();
        $this->assertSame($old->addSeconds(5400)->timestamp, $owner->expires_at->timestamp);
        $this->assertSame($activation->timestamp, $owner->activated_at->timestamp);
        $this->assertSame('RENEWAL-DEVICE-001', $owner->hwid);
        $this->assertDatabaseCount('license_keys', $keys);
        $this->assertDatabaseCount('financial_transactions', 1);
    }

    public function test_expired_and_unactivated_renewal_follow_correct_duration_base(): void
    {
        foreach ([false, true] as $activate) {
            $this->freezeTime();
            [$owner,$key] = app(LicenseService::class)->issue(['label' => 'Renew', 'days' => 1], null, null);
            if ($activate) {
                app(LicenseService::class)->check($key, 'EXPIRED-RENEW-001', true, null);
                $this->travel(2)->days();
            }
            $product = LicenseProduct::factory()->create(['game_ids' => $owner->games->modelKeys(), 'duration_seconds' => 2100]);
            $order = app(PurchaseService::class)->order($owner, $product->id, (string) Str::uuid(), null, true);
            app(PurchaseService::class)->settle($order, User::factory()->create()->id, 'REF-'.Str::uuid(), null);
            $owner->refresh();
            if ($activate) {
                $this->assertSame(now()->addSeconds(2100)->timestamp, $owner->expires_at->timestamp);
            } else {
                $this->assertNull($owner->expires_at);
                $this->assertNull($owner->activated_at);
                $this->assertSame(88500, $owner->duration_seconds);
            }$this->travelBack();
        }
    }

    public function test_renewal_of_revoked_key_rolls_back_payment_and_cannot_be_redirected_to_another_key(): void
    {
        [$owner] = app(LicenseService::class)->issue(['label' => 'Owner', 'days' => 1], null, null);
        $product = LicenseProduct::factory()->create(['game_ids' => $owner->games->modelKeys()]);
        $order = app(PurchaseService::class)->order($owner, $product->id, (string) Str::uuid(), null, true);
        $owner->update(['revoked_at' => now()]);
        try {
            app(PurchaseService::class)->settle($order, User::factory()->create()->id, 'REVOKED-REF', null);
            $this->fail('Must reject revoked renewal.');
        } catch (ValidationException) {
        }$this->assertSame('pending', $order->fresh()->status);
        $this->assertDatabaseCount('financial_transactions', 0);
    }

    public function test_guest_receipts_are_session_scoped_and_success_url_cannot_fulfill_order(): void
    {
        $order = PurchaseOrder::factory()->create(['owner_license_id' => null, 'guest_token_hash' => hash('sha256', 'guest-secret')]);
        $this->get('/checkout/'.$order->number)->assertNotFound();
        $this->withSession(['checkout_secret' => 'guest-secret'])->get('/checkout/'.$order->number.'?status=paid')->assertOk()->assertSee('Selesaikan langkah terakhir.');
        $this->assertSame('pending', $order->fresh()->status);
        $this->assertDatabaseCount('financial_transactions', 0);
    }

    public function test_payment_returns_redirect_only_to_session_owned_invoice_without_fulfilling(): void
    {
        $order = $this->order('sandbox', ['owner_license_id' => null, 'guest_token_hash' => hash('sha256', 'guest-secret')]);
        $keys = LicenseKey::count();
        foreach (['success', 'cancel'] as $result) {
            $this->withSession(['checkout_secret' => 'guest-secret', 'payment_return_order' => $order->number])
                ->get('/payment/'.$result.'?status=paid&order_id=untrusted')
                ->assertRedirect(route('checkout.receipt', ['order' => $order->number]));
        }
        $this->assertSame('pending', $order->fresh()->status);
        $this->assertDatabaseCount('license_keys', $keys);
        $this->assertDatabaseCount('financial_transactions', 0);
    }

    public function test_payment_return_rejects_missing_or_foreign_session(): void
    {
        $order = $this->order('sandbox', ['owner_license_id' => null, 'guest_token_hash' => hash('sha256', 'guest-secret')]);
        $this->get('/payment/success?order_id='.$order->number)->assertRedirect(route('home'));
        $this->withSession(['checkout_secret' => 'other-secret', 'payment_return_order' => $order->number])
            ->get('/payment/cancel')->assertRedirect(route('home'))->assertSessionMissing('payment_return_order');
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_gateway_secrets_are_encrypted_never_rendered_and_blank_keeps_existing(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin)->put('/payment-settings', ['mode' => 'sandbox', 'sandbox_api_key' => 'sensitive-test-key', 'sandbox_webhook_token' => 'sensitive-test-token'])->assertSessionHasNoErrors();
        $raw = Setting::where('name', 'sumopod_sandbox_api_key')->value('value');
        $this->assertNotSame('sensitive-test-key', $raw);
        $this->assertSame('sensitive-test-key', PaymentSettings::secret('api_key', 'sandbox'));
        $this->get('/payment-settings')->assertOk()->assertDontSee('sensitive-test-key')->assertDontSee('sensitive-test-token');
        $this->put('/payment-settings', ['mode' => 'sandbox', 'sandbox_api_key' => ''])->assertSessionHasNoErrors();
        $this->assertSame('sensitive-test-key', PaymentSettings::secret('api_key', 'sandbox'));
    }

    public function test_enabled_gateway_explains_missing_webhook_and_existing_checkout_becomes_available_after_configuration(): void
    {
        $admin = User::factory()->create();
        $order = PurchaseOrder::factory()->create();
        $this->actingAs($admin)->put('/payment-settings', ['mode' => 'sandbox', 'enabled' => '1', 'sandbox_api_key' => 'readiness-test-api-key'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', fn (string $message): bool => str_contains($message, 'verifikasi webhook Demo / Sandbox belum dikonfigurasi'));

        $this->assertFalse(PaymentSettings::ready());
        $this->get('/payment-settings')->assertOk()->assertSee('Webhook belum diatur')->assertSee('Metode pembayaran belum tersedia.');
        $this->get('/checkout/'.$order->number)->assertOk()
            ->assertSee('Pembayaran online sudah diaktifkan, tetapi verifikasi webhook Demo / Sandbox belum dikonfigurasi.')
            ->assertDontSee('id="checkout-method"', false)
            ->assertDontSee('Pembayaran online belum diaktifkan.');
        $this->post('/checkout/'.$order->number.'/payment', ['method' => 'QRIS'])->assertSessionHasErrors('payment');
        Http::assertNothingSent();

        $this->put('/payment-settings', ['mode' => 'sandbox', 'enabled' => '1', 'sandbox_webhook_token' => 'readiness-test-webhook-token'])->assertSessionHasNoErrors();
        $this->assertTrue(PaymentSettings::ready());
        $this->get('/payment-settings')->assertOk()->assertSee('Konfigurasi pembayaran siap.')->assertSee('Kredensial lengkap')->assertDontSee('readiness-test-api-key')->assertDontSee('readiness-test-webhook-token');
        $this->get('/checkout/'.$order->number)->assertOk()->assertSee('id="checkout-method"', false)->assertSee('QRIS · semua aplikasi pembayaran')->assertSee('QRIS Instant · demo');
        $this->assertSame('pending', $order->fresh()->status);
        $this->assertNull($order->fresh()->gateway_payment_id);
        $this->assertDatabaseCount('financial_transactions', 0);
        Http::assertNothingSent();
    }

    public function test_readiness_distinguishes_disabled_missing_api_key_and_valid_signing_secret(): void
    {
        $this->assertSame('Pembayaran online belum diaktifkan oleh admin.', PaymentSettings::unavailableReason());
        Setting::updateOrCreate(['name' => 'payment_enabled'], ['value' => '1']);
        $this->assertStringContainsString('API key Demo / Sandbox belum dikonfigurasi', PaymentSettings::unavailableReason());
        $this->configure('sandbox');
        $this->assertTrue(PaymentSettings::ready());
        $this->assertNull(PaymentSettings::unavailableReason());
    }

    public function test_production_remains_unavailable_on_local_server_with_clear_explanation(): void
    {
        $this->configure('production');
        $this->app->detectEnvironment(fn (): string => 'local');
        $this->assertFalse(PaymentSettings::ready());
        $this->assertStringContainsString('Production tidak tersedia di server lokal', PaymentSettings::unavailableReason());
    }

    public function test_manual_verification_requires_payment_details_and_cannot_override_uncertain_gateway(): void
    {
        $order = PurchaseOrder::factory()->create();
        $this->actingAs(User::factory()->create())->post('/orders/'.$order->id.'/settle', ['payment_reference' => 'REF'])->assertSessionHasErrors(['payment_method', 'payer_name', 'paid_at']);
        $this->assertDatabaseCount('financial_transactions', 0);
        $order->update(['gateway_status' => 'uncertain']);
        $this->post('/orders/'.$order->id.'/settle', ['payment_reference' => 'REF', 'payment_method' => 'cash', 'payer_name' => 'Buyer', 'paid_at' => now('Asia/Jakarta')->format('Y-m-d\TH:i')])->assertSessionHasErrors('payment_reference');
        $this->assertDatabaseCount('financial_transactions', 0);
    }
}
