<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\LicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberPortalTest extends TestCase
{
    use RefreshDatabase;

    private function issue(): array
    {
        return app(LicenseService::class)->issue(['label' => 'Member A', 'days' => 1], null, '127.0.0.1');
    }

    public function test_member_login_does_not_activate_key_or_start_expiry(): void
    {
        [$license,$key] = $this->issue();
        $this->get('/member/login')->assertOk();
        $this->post('/member/login', ['key' => $key])->assertRedirect('/member')->assertSessionHas('member_license_id', $license->id);
        $this->get('/member')->assertOk()->assertSee('Durasi realtime')->assertSee('Belum diaktifkan')->assertSee($key);
        $this->assertNull($license->fresh()->activated_at);
        $this->assertNull($license->fresh()->expires_at);
    }

    public function test_member_session_is_recognized_on_public_shop_and_login_page(): void
    {
        [$license,$key] = $this->issue();
        $this->post('/member/login', ['key' => $key])->assertRedirect('/member');
        foreach (['/', '/shop'] as $path) {
            $this->get($path)->assertOk()->assertSee('Dashboard member')->assertSessionHas('member_license_id', $license->id);
        }
        $this->get('/member/login')->assertRedirect('/member');
        $this->get('/member')->assertOk();
        $this->post('/member/logout')->assertStatus(303)->assertRedirect('/member/login')->assertSessionMissing('member_license_id');
        $this->get('/shop')->assertOk()->assertSee('Masuk member');
        $this->get('/member/login')->assertOk();
    }

    public function test_invalid_and_revoked_keys_cannot_login_and_key_is_not_flashed(): void
    {
        [$license,$key] = $this->issue();
        $this->post('/member/login', ['key' => 'not-a-key'])->assertSessionHasErrors('key')->assertSessionMissing('_old_input.key');
        $license->update(['revoked_at' => now()]);
        $this->post('/member/login', ['key' => $key])->assertSessionHasErrors('key');
    }

    public function test_member_login_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/member/login', ['key' => 'invalid-key'])->assertRedirect();
        }
        $this->post('/member/login', ['key' => 'invalid-key'])->assertTooManyRequests();
    }

    public function test_member_only_sees_their_own_hwid_and_key(): void
    {
        [$license,$key] = $this->issue();
        [$other,$otherKey] = $this->issue();
        app(LicenseService::class)->check($key, 'DEVICE-ALPHA-001', true, '127.0.0.1');
        $this->post('/member/login', ['key' => $key])->assertRedirect('/member');
        $this->get('/member')->assertOk()->assertSee('DEVICE-ALPHA-001')->assertSee($key)->assertDontSee($otherKey)->assertSee('data-member-expires', false);
    }

    public function test_member_can_reset_only_their_own_hwid_and_duration_is_unchanged(): void
    {
        $this->freezeTime();
        [$license,$key] = $this->issue();
        [$other,$otherKey] = $this->issue();
        app(LicenseService::class)->check($key, 'DEVICE-ALPHA-001', true, '127.0.0.1');
        app(LicenseService::class)->check($otherKey, 'DEVICE-BRAVO-002', true, '127.0.0.1');
        $expiry = $license->fresh()->expires_at;
        $activated = $license->fresh()->activated_at;
        $this->post('/member/login', ['key' => $key]);
        $this->travel(10)->minutes();
        $this->post('/member/reset-hwid', ['license_id' => $other->id])->assertRedirect();
        $this->assertNull($license->fresh()->hwid);
        $this->assertSame('DEVICE-BRAVO-002', $other->fresh()->hwid);
        $this->assertTrue($license->fresh()->expires_at->equalTo($expiry));
        $this->assertTrue($license->fresh()->activated_at->equalTo($activated));
        $this->post('/member/reset-hwid')->assertTooManyRequests();
    }

    public function test_member_cannot_create_edit_extend_delete_or_revoke_keys(): void
    {
        [$license,$key] = $this->issue();
        $this->post('/member/login', ['key' => $key]);
        $this->get('/dashboard')->assertRedirect('/login');
        $this->post('/licenses', ['label' => 'attack', 'days' => 30])->assertRedirect('/login');
        $this->put('/licenses/'.$license->id, ['label' => 'attack'])->assertRedirect('/login');
        $this->delete('/licenses/'.$license->id)->assertRedirect('/login');
        $this->post('/licenses/'.$license->id.'/extend', ['days' => 30])->assertRedirect('/login');
        $this->post('/licenses/'.$license->id.'/revoke')->assertRedirect('/login');
        $this->put('/settings', ['whatsapp_number' => '621234567890'])->assertRedirect('/login');
        $this->assertSame(86400, $license->fresh()->duration_seconds);
    }

    public function test_revoked_and_deleted_keys_invalidate_existing_member_session(): void
    {
        [$license,$key] = $this->issue();
        $this->post('/member/login', ['key' => $key]);
        $license->update(['revoked_at' => now()]);
        $this->get('/member')->assertRedirect('/member/login')->assertSessionMissing('member_license_id');
        [$second,$secondKey] = $this->issue();
        $this->post('/member/login', ['key' => $secondKey]);
        $second->delete();
        $this->get('/member')->assertRedirect('/member/login')->assertSessionMissing('member_license_id');
    }

    public function test_renewal_link_uses_admin_whatsapp_without_exposing_key(): void
    {
        Setting::create(['name' => 'whatsapp_number', 'value' => '6281234567890']);
        [$license,$key] = $this->issue();
        $this->post('/member/login', ['key' => $key]);
        $this->get('/member')->assertOk()->assertSee('https://wa.me/6281234567890?text=', false);
        $this->assertStringNotContainsString($key, Setting::renewalUrl($license));
    }

    public function test_expired_member_can_see_zero_duration_but_cannot_reset_or_extend(): void
    {
        [$license,$key] = $this->issue();
        $license->update(['activated_at' => now()->subDays(2), 'expires_at' => now()->subDay()]);
        $this->post('/member/login', ['key' => $key])->assertRedirect('/member');
        $this->get('/member')->assertOk()->assertSee('Masa berlaku sudah habis');
        $this->post('/member/reset-hwid')->assertSessionHasErrors('hwid');
    }

    public function test_member_logout_clears_access_and_guests_cannot_reset(): void
    {
        [$license,$key] = $this->issue();
        $this->post('/member/login', ['key' => $key]);
        $this->post('/member/logout')->assertStatus(303)->assertRedirect('/member/login')->assertSessionMissing('member_license_id');
        $this->get('/member')->assertRedirect('/member/login');
        $this->getJson('/member/status')->assertUnauthorized();
        $this->get('/shop')->assertOk()->assertSee('Masuk member');
        $this->get('/member/login')->assertOk();
        $this->post('/member/reset-hwid')->assertRedirect('/member/login');
    }

    public function test_logout_clears_checkout_credentials_and_keeps_admin_session(): void
    {
        [$license] = $this->issue();
        $admin = \App\Models\User::factory()->create();
        $this->actingAs($admin)->withSession(['member_license_id' => $license->id, 'checkout_secret' => 'guest-secret', 'payment_return_order' => 'KZ-TEST']);
        $this->post('/member/logout')->assertStatus(303)->assertSessionMissing('member_license_id')->assertSessionMissing('checkout_secret')->assertSessionMissing('payment_return_order');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_member_live_status_is_scoped_and_does_not_reveal_license_secret(): void
    {
        [$license,$key] = $this->issue();
        $this->getJson('/member/status')->assertUnauthorized();
        $this->post('/member/login', ['key' => $key]);
        $this->getJson('/member/status')->assertOk()->assertJsonPath('data.expires_at', null)->assertDontSee($key);
        app(LicenseService::class)->check($key, 'DEVICE-ALPHA-001', true, '127.0.0.1');
        $this->getJson('/member/status')->assertOk()->assertJsonPath('data.hwid', 'DEVICE-ALPHA-001')->assertJsonPath('data.status', 'active')->assertDontSee($key);
    }
}
