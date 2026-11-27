<?php

namespace Tests\Feature;

use App\Models\PurchaseOrder;
use App\Models\Setting;
use App\Models\User;
use App\Providers\AppServiceProvider;
use App\Services\LicenseService;
use App\Services\SumoPodService;
use App\Support\PaymentSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_html_has_fresh_csp_nonce_and_security_headers_without_breaking_bundled_assets(): void
    {
        $first = $this->get('/login')->assertOk()->assertHeader('X-Frame-Options', 'DENY')->assertHeader('X-Content-Type-Options', 'nosniff')->assertHeader('Cross-Origin-Opener-Policy', 'same-origin');
        $policy = $first->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("object-src 'none'", $policy);
        $this->assertStringContainsString("form-action 'self'", $policy);
        $this->assertStringContainsString("frame-ancestors 'none'", $policy);
        $this->assertStringContainsString("script-src-attr 'none'", $policy);
        $this->assertStringNotContainsString('unsafe-eval', $policy);
        preg_match("/nonce-([^']+)/", $policy, $matches);
        $this->assertNotEmpty($matches[1]);
        $first->assertSee('nonce="'.$matches[1].'"', false)->assertSee('theme.js');
        $second = $this->get('/login')->assertOk();
        $this->assertNotSame($policy, $second->headers->get('Content-Security-Policy'));
        $first->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_production_rejects_untrusted_host_and_sends_hsts_only_over_https(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');
        config(['app.url' => 'https://secure.example.test']);
        $this->get('https://evil.example.test/login')->assertStatus(400);
        $this->get('https://secure.example.test/login')->assertOk()->assertHeader('Strict-Transport-Security', 'max-age=31536000');
        $this->get('http://secure.example.test/login')->assertOk()->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_production_forces_safe_debug_and_session_settings(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');
        config(['app.debug' => true, 'session.secure' => false, 'session.http_only' => false, 'session.encrypt' => false]);
        $this->app->getProvider(AppServiceProvider::class)->boot();
        $this->assertFalse(config('app.debug'));
        $this->assertTrue(config('session.secure'));
        $this->assertTrue(config('session.http_only'));
        $this->assertTrue(config('session.encrypt'));
        $this->assertStringStartsWith('https://', route('login'));
    }

    public function test_malformed_login_and_api_credentials_are_validation_errors_instead_of_server_errors(): void
    {
        $this->postJson('/login', ['email' => ['invalid'], 'password' => 'ExamplePassword42'])->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->postJson('/member/login', ['key' => ['invalid']])->assertUnprocessable()->assertJsonValidationErrors('key');
        $this->postJson('/api/v1/licenses/validate', ['key' => ['invalid'], 'hwid' => 'TEST-DEVICE-001', 'game_code' => 'rules-of-survival'])->assertUnprocessable()->assertJsonValidationErrors('key');
    }

    public function test_admin_account_rate_limit_also_applies_across_different_ip_addresses(): void
    {
        for ($attempt = 1; $attempt <= 10; $attempt++) {
            $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.'.$attempt])->post('/login', ['email' => 'limited@example.test', 'password' => 'IncorrectPassword42'])->assertRedirect();
        }
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.11'])->post('/login', ['email' => 'LIMITED@example.test', 'password' => 'IncorrectPassword42'])->assertTooManyRequests();
    }

    public function test_admin_password_is_hashed_and_never_in_admin_listing_or_model_serialization(): void
    {
        $admin = User::factory()->create();
        $password = 'DistinctPassword-42-Secret';
        $this->actingAs($admin)->post('/admins', ['name' => 'Security Admin', 'email' => 'security@example.test', 'password' => $password, 'password_confirmation' => $password])->assertSessionHasNoErrors();
        $created = User::where('email', 'security@example.test')->firstOrFail();
        $this->assertNotSame($password, $created->getRawOriginal('password'));
        $this->assertTrue(Hash::check($password, $created->password));
        $this->assertArrayNotHasKey('password', $created->toArray());
        $this->get('/admins')->assertOk()->assertDontSee($password)->assertDontSee($created->password);
    }

    public function test_password_byte_limit_and_null_byte_are_validated_before_bcrypt_hashing(): void
    {
        $this->actingAs(User::factory()->create());
        foreach (['Aa1'.str_repeat('x', 70), 'Aa1'.str_repeat('界', 24), "StrongPassword42\0Bad"] as $index => $password) {
            $this->post('/admins', ['name' => 'Invalid password', 'email' => 'invalid-'.$index.'@example.test', 'password' => $password, 'password_confirmation' => $password])->assertSessionHasErrors('password');
        }
        $this->assertDatabaseCount('users', 1);
        $this->post('/logout')->assertRedirect('/login');
        $this->postJson('/login', ['email' => 'test@example.test', 'password' => "StrongPassword42\0Bad"])->assertUnprocessable();
    }

    public function test_member_session_id_changes_and_old_session_storage_is_destroyed_on_login_and_logout(): void
    {
        [$license, $key] = app(LicenseService::class)->issue(['label' => 'Session security', 'days' => 1], null, null);
        $session = $this->app['session']->driver();
        $session->put('marker', 'previous-session');
        $session->save();
        $oldId = $session->getId();
        $handler = $session->getHandler();
        $this->withCookie($session->getName(), $oldId)->post('/member/login', ['key' => $key])->assertRedirect('/member')->assertSessionHas('member_license_id', $license->id);
        $this->assertNotSame($oldId, $session->getId());
        $this->assertEmpty($handler->read($oldId));
        $session->save();
        $loggedInId = $session->getId();
        $this->withCookie($session->getName(), $loggedInId)->post('/member/logout')->assertRedirect('/member/login')->assertSessionMissing('member_license_id');
        $this->assertNotSame($loggedInId, $session->getId());
        $this->assertEmpty($handler->read($loggedInId));
    }

    public function test_web_forms_require_csrf_token_outside_testing_environment(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');
        $this->post('/login', ['email' => 'test@example.test', 'password' => 'ExamplePassword42'])->assertStatus(419);
    }

    public function test_untrusted_names_are_escaped_in_admin_html(): void
    {
        $payload = '<img src=x onerror=alert(1)>';
        $this->actingAs(User::factory()->create(['name' => $payload]));
        $this->get('/admins')->assertOk()->assertSee($payload)->assertDontSee($payload, false);
    }

    public function test_gateway_client_does_not_follow_redirects_and_does_not_fulfill_redirected_payment(): void
    {
        Http::preventStrayRequests();
        Setting::updateOrCreate(['name' => 'payment_mode'], ['value' => 'sandbox']);
        Setting::updateOrCreate(['name' => 'payment_enabled'], ['value' => '1']);
        PaymentSettings::saveSecret('api_key', 'security-test-api-key', 'sandbox');
        PaymentSettings::saveSecret('webhook_token', 'security-test-webhook-token', 'sandbox');
        $optionsSeen = null;
        Http::fake(function ($request, array $options) use (&$optionsSeen) {
            $optionsSeen = $options;

            return Http::response('', 302, ['Location' => 'https://external.example.test/collect']);
        });
        $order = PurchaseOrder::factory()->create();
        try {
            app(SumoPodService::class)->createLink($order);
            $this->fail('Redirected gateway request must not be accepted.');
        } catch (ValidationException) {
            $this->assertFalse($optionsSeen['allow_redirects']);
            $this->assertSame('uncertain', $order->fresh()->gateway_status);
            $this->assertSame('pending', $order->fresh()->status);
            $this->assertNull($order->fresh()->issued_license_id);
            $this->assertDatabaseCount('financial_transactions', 0);
            Http::assertSentCount(1);
        }
    }
}
