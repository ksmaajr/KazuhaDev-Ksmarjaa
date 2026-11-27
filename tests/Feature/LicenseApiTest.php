<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicenseApiTest extends TestCase
{
    use RefreshDatabase;

    private function issue(int $days = 7): array
    {
        return app(LicenseService::class)->issue(['label' => 'Test license', 'days' => $days], null, '127.0.0.1');
    }

    public function test_first_request_binds_hwid_and_encrypts_its_storage(): void
    {
        $this->freezeTime();
        [$license,$key] = $this->issue();
        $this->postJson('/api/v1/licenses/validate', ['game_code' => 'rules-of-survival', 'key' => $key, 'hwid' => 'DEVICE-ALPHA-001'])->assertOk()->assertJsonPath('success', true)->assertJsonPath('data.newly_bound', true)->assertJsonPath('data.remaining_seconds', 604800);
        $license->refresh();
        $this->assertSame('DEVICE-ALPHA-001', $license->hwid);
        $this->assertNotSame('DEVICE-ALPHA-001', $license->getRawOriginal('hwid'));
        $this->assertNotNull($license->activated_at);
        $this->assertDatabaseHas('license_events', ['license_key_id' => $license->id, 'action' => 'activated']);
    }

    public function test_same_hwid_passes_without_extending_expiry_or_changing_activation(): void
    {
        $this->freezeTime();
        [$license,$key] = $this->issue();
        $this->postJson('/api/v1/licenses/validate', ['game_code' => 'rules-of-survival', 'key' => $key, 'hwid' => 'DEVICE-ALPHA-001'])->assertOk();
        $activation = $license->fresh()->activated_at;
        $expiry = $license->fresh()->expires_at;
        $this->travel(2)->minutes();
        $this->postJson('/api/v1/licenses/validate', ['game_code' => 'rules-of-survival', 'key' => $key, 'hwid' => 'DEVICE-ALPHA-001'])->assertOk()->assertJsonPath('data.newly_bound', false)->assertJsonPath('data.remaining_seconds', 604680);
        $this->assertTrue($license->fresh()->activated_at->equalTo($activation));
        $this->assertTrue($license->fresh()->expires_at->equalTo($expiry));
    }

    public function test_different_hwid_is_denied_and_cannot_overwrite_binding(): void
    {
        [$license,$key] = $this->issue();
        $this->postJson('/api/v1/licenses/validate', ['game_code' => 'rules-of-survival', 'key' => $key, 'hwid' => 'DEVICE-ALPHA-001'])->assertOk();
        $this->postJson('/api/v1/licenses/validate', ['game_code' => 'rules-of-survival', 'key' => $key, 'hwid' => 'DEVICE-BRAVO-002'])->assertForbidden()->assertJsonPath('success', false)->assertJsonPath('code', 'hwid_mismatch');
        $this->assertSame('DEVICE-ALPHA-001', $license->fresh()->hwid);
    }

    public function test_expiry_at_exact_boundary_denies_and_does_not_bind(): void
    {
        $this->freezeTime();
        [$license,$key] = $this->issue(1);
        $this->postJson('/api/v1/licenses/validate', ['game_code' => 'rules-of-survival', 'key' => $key, 'hwid' => 'DEVICE-ALPHA-001'])->assertOk();
        app(LicenseService::class)->reset($license, null, '127.0.0.1');
        $this->travelTo($license->fresh()->expires_at);
        $this->postJson('/api/v1/licenses/validate', ['game_code' => 'rules-of-survival', 'key' => $key, 'hwid' => 'DEVICE-ALPHA-001'])->assertForbidden()->assertJsonPath('code', 'expired');
        $this->assertNull($license->fresh()->hwid);
    }

    public function test_key_is_denied_after_revoke(): void
    {
        [$license,$key] = $this->issue();
        app(LicenseService::class)->revoke($license, null, '127.0.0.1');
        $this->postJson('/api/v1/licenses/validate', ['game_code' => 'rules-of-survival', 'key' => $key, 'hwid' => 'DEVICE-ALPHA-001'])->assertForbidden()->assertJsonPath('code', 'revoked');
    }

    public function test_well_formed_unknown_key_is_denied(): void
    {
        $this->postJson('/api/v1/licenses/validate', ['game_code' => 'rules-of-survival', 'key' => 'KF-AAAAAAAA-BBBBBBBB-CCCCCCCC-DDDDDDDD', 'hwid' => 'DEVICE-ALPHA-001'])->assertUnauthorized()->assertJsonPath('code', 'invalid_key');
    }

    public function test_bad_and_missing_inputs_return_json_validation_errors(): void
    {
        $this->postJson('/api/v1/licenses/validate', [])->assertUnprocessable()->assertJsonValidationErrors(['key', 'hwid']);
        $this->postJson('/api/v1/licenses/validate', ['game_code' => 'rules-of-survival', 'key' => 'invalid', 'hwid' => 'bad hwid'])->assertUnprocessable()->assertJsonValidationErrors(['key', 'hwid']);
    }

    public function test_hwid_is_case_sensitive(): void
    {
        [$license,$key] = $this->issue();
        $this->postJson('/api/v1/licenses/validate', ['game_code' => 'rules-of-survival', 'key' => $key, 'hwid' => 'DEVICE-ALPHA-001'])->assertOk();
        $this->postJson('/api/v1/licenses/validate', ['game_code' => 'rules-of-survival', 'key' => $key, 'hwid' => 'device-alpha-001'])->assertForbidden();
    }

    public function test_status_does_not_activate_unbound_key(): void
    {
        [$license,$key] = $this->issue();
        $this->withHeaders(['Authorization' => 'Bearer '.$key, 'X-Game' => 'rules-of-survival', 'X-HWID' => 'DEVICE-ALPHA-001'])->getJson('/api/v1/licenses/status')->assertStatus(409)->assertJsonPath('code', 'activation_required');
        $this->assertNull($license->fresh()->activated_at);
    }

    public function test_status_requires_both_credentials_and_never_accepts_query_keys(): void
    {
        [$license,$key] = $this->issue();
        $this->getJson('/api/v1/licenses/status?key='.$key.'&hwid=DEVICE-ALPHA-001')->assertUnprocessable();
        $this->withHeaders(['Authorization' => 'Bearer '.$key])->getJson('/api/v1/licenses/status')->assertUnprocessable();
    }

    public function test_status_checks_same_hwid_without_updating_last_seen(): void
    {
        $this->freezeTime();
        [$license,$key] = $this->issue();
        $this->postJson('/api/v1/licenses/validate', ['game_code' => 'rules-of-survival', 'key' => $key, 'hwid' => 'DEVICE-ALPHA-001'])->assertOk();
        $seen = $license->fresh()->last_seen_at;
        $this->travel(1)->minutes();
        $this->withHeaders(['Authorization' => 'Bearer '.$key, 'X-Game' => 'rules-of-survival', 'X-HWID' => 'DEVICE-ALPHA-001'])->getJson('/api/v1/licenses/status')->assertOk()->assertJsonPath('data.remaining_seconds', 604740)->assertHeader('Cache-Control');
        $this->assertTrue($license->fresh()->last_seen_at->equalTo($seen));
        $this->withHeaders(['Authorization' => 'Bearer '.$key, 'X-Game' => 'rules-of-survival', 'X-HWID' => 'DEVICE-BRAVO-002'])->getJson('/api/v1/licenses/status')->assertForbidden();
    }

    public function test_reset_allows_new_device_without_extending_expiry_or_first_activation(): void
    {
        $this->freezeTime();
        [$license,$key] = $this->issue();
        $this->postJson('/api/v1/licenses/validate', ['game_code' => 'rules-of-survival', 'key' => $key, 'hwid' => 'DEVICE-ALPHA-001'])->assertOk();
        $first = $license->fresh()->activated_at;
        $expiry = $license->fresh()->expires_at;
        $this->travel(1)->hours();
        $this->actingAs(User::factory()->create())->post('/licenses/'.$license->id.'/reset-hwid')->assertRedirect();
        $this->assertNull($license->fresh()->hwid);
        $this->postJson('/api/v1/licenses/validate', ['game_code' => 'rules-of-survival', 'key' => $key, 'hwid' => 'DEVICE-BRAVO-002'])->assertOk();
        $this->assertSame('DEVICE-BRAVO-002', $license->fresh()->hwid);
        $this->assertSame(1, $license->fresh()->reset_count);
        $this->assertTrue($license->fresh()->activated_at->equalTo($first));
        $this->assertTrue($license->fresh()->expires_at->equalTo($expiry));
    }

    public function test_reset_cannot_restore_expired_or_revoked_key(): void
    {
        [$license,$key] = $this->issue();
        $license->update(['revoked_at' => now()]);
        app(LicenseService::class)->reset($license, null, '127.0.0.1');
        $this->postJson('/api/v1/licenses/validate', ['game_code' => 'rules-of-survival', 'key' => $key, 'hwid' => 'DEVICE-ALPHA-001'])->assertForbidden()->assertJsonPath('code', 'revoked');
    }

    public function test_api_returns_saved_heartbeat_interval(): void
    {
        Setting::create(['name' => 'heartbeat_interval', 'value' => '90']);
        [$license,$key] = $this->issue();
        $this->postJson('/api/v1/licenses/validate', ['game_code' => 'rules-of-survival', 'key' => $key, 'hwid' => 'DEVICE-ALPHA-001'])->assertOk()->assertJsonPath('data.heartbeat_interval', 90);
    }

    public function test_key_lookup_uses_hash_and_responses_hide_encrypted_secrets(): void
    {
        [$license,$key] = $this->issue();
        $this->assertSame(hash('sha256', $key), $license->key_hash);
        $this->assertSame($key, $license->key_value);
        $this->assertNotSame($key, $license->getRawOriginal('key_value'));
        $response = $this->postJson('/api/v1/licenses/validate', ['game_code' => 'rules-of-survival', 'key' => $key, 'hwid' => 'DEVICE-ALPHA-001'])->assertOk();
        $response->assertDontSee($key)->assertDontSee('DEVICE-ALPHA-001')->assertDontSee('key_hash');
    }

    public function test_api_is_rate_limited_by_license(): void
    {
        [$license,$key] = $this->issue();
        for ($i = 0; $i < 30; $i++) {
            $this->postJson('/api/v1/licenses/validate', ['game_code' => 'rules-of-survival', 'key' => $key, 'hwid' => 'DEVICE-ALPHA-001'])->assertOk();
        }
        $this->postJson('/api/v1/licenses/validate', ['game_code' => 'rules-of-survival', 'key' => $key, 'hwid' => 'DEVICE-ALPHA-001'])->assertTooManyRequests();
    }

    public function test_unactivated_key_keeps_full_duration_even_after_waiting_forty_days(): void
    {
        $this->freezeTime();
        [$license,$key] = $this->issue(1);
        $this->assertNull($license->expires_at);
        $this->travel(40)->days();
        $this->postJson('/api/v1/licenses/validate', ['game_code' => 'rules-of-survival', 'key' => $key, 'hwid' => 'DEVICE-ALPHA-001'])->assertOk()->assertJsonPath('data.remaining_seconds', 86400);
        $this->assertSame(now()->getTimestamp(), $license->fresh()->activated_at->getTimestamp());
        $this->assertSame(now()->addDay()->getTimestamp(), $license->fresh()->expires_at->getTimestamp());
    }
}
