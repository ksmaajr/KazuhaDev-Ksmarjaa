<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLicenseActionsTest extends TestCase
{
    use RefreshDatabase;

    private function issue(): array
    {
        return app(LicenseService::class)->issue(['label' => 'Original', 'days' => 7], null, '127.0.0.1');
    }

    public function test_admin_edits_label_notes_and_pending_duration(): void
    {
        [$license,$key] = $this->issue();
        $this->actingAs(User::factory()->create());
        $this->put('/licenses/'.$license->id, ['label' => 'Updated', 'notes' => 'Internal note', 'days' => 3])->assertSessionHasNoErrors();
        $this->assertSame('Updated', $license->fresh()->label);
        $this->assertSame('Internal note', $license->fresh()->notes);
        $this->assertSame(259200, $license->fresh()->duration_seconds);
        $this->assertNull($license->fresh()->expires_at);
        $this->assertSame($key, $license->fresh()->key_value);
        $this->assertDatabaseHas('license_events', ['license_key_id' => $license->id, 'action' => 'edited']);
    }

    public function test_edit_cannot_overwrite_active_duration(): void
    {
        [$license,$key] = $this->issue();
        app(LicenseService::class)->check($key, 'DEVICE-ALPHA-001', true, '127.0.0.1');
        $expiry = $license->fresh()->expires_at;
        $this->actingAs(User::factory()->create());
        $this->put('/licenses/'.$license->id, ['label' => 'Changed', 'days' => 30])->assertSessionHasErrors('days');
        $this->assertSame('Original', $license->fresh()->label);
        $this->assertTrue($license->fresh()->expires_at->equalTo($expiry));
        $this->put('/licenses/'.$license->id, ['label' => 'Metadata only', 'notes' => 'Note'])->assertSessionHasNoErrors();
    }

    public function test_admin_extends_active_key_without_changing_hwid_or_activation(): void
    {
        $this->freezeTime();
        [$license,$key] = $this->issue();
        app(LicenseService::class)->check($key, 'DEVICE-ALPHA-001', true, '127.0.0.1');
        $expiry = $license->fresh()->expires_at;
        $activated = $license->fresh()->activated_at;
        $this->actingAs(User::factory()->create())->post('/licenses/'.$license->id.'/extend', ['days' => 3])->assertSessionHasNoErrors();
        $this->assertTrue($license->fresh()->expires_at->equalTo($expiry->addDays(3)));
        $this->assertTrue($license->fresh()->activated_at->equalTo($activated));
        $this->assertSame('DEVICE-ALPHA-001', $license->fresh()->hwid);
        $this->assertDatabaseHas('license_events', ['license_key_id' => $license->id, 'action' => 'extended']);
    }

    public function test_expired_extension_starts_now_and_pending_extension_does_not_activate(): void
    {
        $this->freezeTime();
        [$license,$key] = $this->issue();
        $this->actingAs(User::factory()->create())->post('/licenses/'.$license->id.'/extend', ['days' => 3])->assertSessionHasNoErrors();
        $this->assertSame(864000, $license->fresh()->duration_seconds);
        $this->assertNull($license->fresh()->expires_at);
        app(LicenseService::class)->check($key, 'DEVICE-ALPHA-001', true, '127.0.0.1');
        $this->travel(20)->days();
        $this->post('/licenses/'.$license->id.'/extend', ['days' => 1])->assertSessionHasNoErrors();
        $this->assertSame(now()->addDay()->getTimestamp(), $license->fresh()->expires_at->getTimestamp());
    }

    public function test_invalid_extensions_and_revoked_extensions_are_rejected(): void
    {
        [$license,$key] = $this->issue();
        $this->actingAs(User::factory()->create());
        foreach ([0, 31, 1.5] as $days) {
            $this->post('/licenses/'.$license->id.'/extend', ['days' => $days])->assertSessionHasErrors('days');
        }
        $license->update(['revoked_at' => now()]);
        $this->post('/licenses/'.$license->id.'/extend', ['days' => 1])->assertSessionHasErrors('days');
        $this->assertSame(604800, $license->fresh()->duration_seconds);
    }

    public function test_deleted_license_disappears_and_cannot_validate_or_login(): void
    {
        [$license,$key] = $this->issue();
        $this->actingAs(User::factory()->create());
        $this->delete('/licenses/'.$license->id)->assertRedirect();
        $this->assertSoftDeleted($license);
        $this->get('/licenses')->assertOk()->assertDontSee('Original');
        $this->postJson('/api/v1/licenses/validate', ['game_code' => 'rules-of-survival', 'key' => $key, 'hwid' => 'DEVICE-ALPHA-001'])->assertUnauthorized()->assertJsonPath('code', 'invalid_key');
        $this->post('/member/login', ['game_code' => 'rules-of-survival', 'key' => $key])->assertSessionHasErrors('key');
        $this->assertDatabaseHas('license_events', ['license_key_id' => $license->id, 'action' => 'deleted']);
        $this->get('/activity')->assertOk()->assertSee('Original');
    }

    public function test_settings_save_valid_wa_number_and_reject_invalid_number(): void
    {
        $this->actingAs(User::factory()->create());
        $this->put('/settings', ['public_url' => 'http://127.0.0.1:8000', 'heartbeat_interval' => 60, 'whatsapp_number' => '+62 8123'])->assertSessionHasErrors('whatsapp_number');
        $this->put('/settings', ['public_url' => 'http://127.0.0.1:8000', 'heartbeat_interval' => 60, 'whatsapp_number' => '6281234567890'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('settings', ['name' => 'whatsapp_number', 'value' => '6281234567890']);
    }

    public function test_admin_redirects_are_not_changed_by_other_member_tabs(): void
    {
        [$license,$key] = $this->issue();
        $this->actingAs(User::factory()->create());
        $this->withSession(['_previous' => ['url' => 'http://localhost/member/status']])->put('/licenses/'.$license->id, ['label' => 'Edited'])->assertRedirect('/licenses');
        $this->withSession(['_previous' => ['url' => 'http://localhost/member/status']])->post('/licenses/'.$license->id.'/extend', ['days' => 1])->assertRedirect('/licenses');
        $this->withSession(['_previous' => ['url' => 'http://localhost/member/status']])->post('/licenses/'.$license->id.'/extend', ['days' => 0])->assertRedirect('/licenses')->assertSessionHasErrors('days');
    }
}
