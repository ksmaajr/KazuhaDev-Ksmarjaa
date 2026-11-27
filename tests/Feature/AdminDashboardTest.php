<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\LicenseKey;
use App\Models\Setting;
use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_read_or_change_admin_pages(): void
    {
        foreach (['/dashboard', '/licenses', '/activity', '/api-console', '/settings'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
        $this->post('/licenses', ['member_name' => 'Test Member', 'label' => 'Unauthorized', 'game_ids' => [Game::where('code', 'rules-of-survival')->firstOrFail()->id], 'days' => 7])->assertRedirect('/login');
        $this->put('/settings', ['public_url' => 'https://evil.example', 'heartbeat_interval' => 60])->assertRedirect('/login');
        $this->assertDatabaseCount('license_keys', 0);
    }

    public function test_login_and_logout_work(): void
    {
        $user = User::factory()->create(['password' => 'correct-password']);
        $this->get('/login')->assertOk()->assertSee('Selamat datang kembali.');
        $this->post('/login', ['email' => $user->email, 'password' => 'correct-password'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_invalid_login_is_rejected_and_password_not_flashed(): void
    {
        $this->post('/login', ['email' => 'missing@example.com', 'password' => 'wrong-password'])->assertSessionHasErrors('email')->assertSessionMissing('_old_input.password');
        $this->assertGuest();
    }

    public function test_login_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'missing@example.com', 'password' => 'wrong-password'])->assertRedirect();
        }
        $this->post('/login', ['email' => 'missing@example.com', 'password' => 'wrong-password'])->assertTooManyRequests();
    }

    public function test_all_admin_views_render_with_real_license_records(): void
    {
        $this->actingAs(User::factory()->create());
        app(LicenseService::class)->issue(['member_name' => 'Test Member', 'label' => 'Client A', 'game_ids' => [Game::where('code', 'rules-of-survival')->firstOrFail()->id], 'days' => 7], null, '127.0.0.1');
        foreach (['/dashboard', '/licenses', '/activity', '/api-console', '/settings'] as $url) {
            $this->get($url)->assertOk()->assertSee('KazuhaDev');
        }
    }

    public function test_bound_hwid_details_render(): void
    {
        [$license,$key] = app(LicenseService::class)->issue(['member_name' => 'Test Member', 'label' => 'Client A', 'game_ids' => [Game::where('code', 'rules-of-survival')->firstOrFail()->id], 'days' => 7], null, '127.0.0.1');
        app(LicenseService::class)->check($key, 'DEVICE-ALPHA-001', true, '127.0.0.1');
        $this->actingAs(User::factory()->create())->get('/licenses')->assertOk()->assertSee('DEVICE-ALPHA-001');
    }

    public function test_admin_issues_whole_day_key_without_starting_expiry_and_can_copy_later(): void
    {
        $this->freezeTime();
        $this->actingAs(User::factory()->create());
        $response = $this->post('/licenses', ['member_name' => 'Test Member', 'label' => 'Premium', 'game_ids' => [Game::where('code', 'rules-of-survival')->firstOrFail()->id], 'days' => 1])->assertRedirect('/licenses')->assertSessionHas('issued_key');
        $plain = session('issued_key');
        $license = LicenseKey::firstOrFail();
        $this->assertSame(86400, $license->duration_seconds);
        $this->assertNull($license->expires_at);
        $this->assertNull($license->activated_at);
        $this->assertSame(hash('sha256', $plain), $license->key_hash);
        $this->get('/licenses')->assertOk()->assertSee($plain);
        $this->get('/licenses')->assertOk()->assertSee($plain);
    }

    public function test_duration_limits_are_enforced(): void
    {
        $this->actingAs(User::factory()->create());
        foreach ([0, 31, 1.5] as $days) {
            $this->post('/licenses', ['member_name' => 'Test Member', 'label' => 'Test', 'game_ids' => [Game::where('code', 'rules-of-survival')->firstOrFail()->id], 'days' => $days])->assertSessionHasErrors('days');
        }
        $this->assertDatabaseCount('license_keys', 0);
        $this->post('/licenses', ['member_name' => 'Test Member', 'label' => 'Valid', 'game_ids' => [Game::where('code', 'rules-of-survival')->firstOrFail()->id], 'days' => 30])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('license_keys', 1);
    }

    public function test_saved_endpoint_is_used_in_docs(): void
    {
        $this->actingAs(User::factory()->create())->put('/settings', ['public_url' => 'https://license.example.com/', 'heartbeat_interval' => 90])->assertSessionHasNoErrors();
        $this->assertSame('https://license.example.com', Setting::valueFor('public_url'));
        $this->get('/api-console')->assertOk()->assertSee('https://license.example.com/api/v1/licenses/validate');
    }

    public function test_invalid_endpoint_components_and_interval_are_rejected(): void
    {
        $this->actingAs(User::factory()->create());
        foreach (['https://example.com/path', 'https://user:pass@example.com', 'https://example.com?key=test', 'ftp://example.com'] as $url) {
            $this->put('/settings', ['public_url' => $url, 'heartbeat_interval' => 60])->assertSessionHasErrors('public_url');
        }
        $this->put('/settings', ['public_url' => 'https://example.com', 'heartbeat_interval' => 29])->assertSessionHasErrors('heartbeat_interval');
        $this->assertDatabaseCount('settings', 0);
    }

    public function test_admin_can_revoke_and_audit_is_retained(): void
    {
        $this->actingAs(User::factory()->create());
        $license = LicenseKey::factory()->create();
        $this->post('/licenses/'.$license->id.'/revoke')->assertRedirect();
        $this->assertNotNull($license->fresh()->revoked_at);
        $this->assertDatabaseHas('license_events', ['license_key_id' => $license->id, 'action' => 'revoked']);
    }

    public function test_search_and_filters_use_saved_data(): void
    {
        LicenseKey::factory()->create(['member_name' => 'Test Member', 'label' => 'Alpha']);
        LicenseKey::factory()->create(['member_name' => 'Test Member', 'label' => 'Beta', 'expires_at' => now()->subSecond()]);
        $this->actingAs(User::factory()->create())->get('/licenses?q=Alpha')->assertOk()->assertSee('Alpha')->assertDontSee('Beta');
        $this->get('/licenses?status=expired')->assertOk()->assertSee('Beta')->assertDontSee('Alpha');
    }

    public function test_admin_commands_do_not_create_known_default_accounts(): void
    {
        $this->artisan('admin:create', ['email' => 'owner@example.com'])->assertSuccessful();
        $this->assertDatabaseHas('users', ['email' => 'owner@example.com']);
        $this->artisan('admin:create', ['email' => 'owner@example.com'])->assertFailed();
        $this->assertDatabaseCount('users', 1);
    }
}
