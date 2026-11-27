<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Services\LicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DllApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_dll_requires_active_bound_key_game_and_same_hwid_and_never_activates_key(): void
    {
        Storage::fake('local');
        $game = Game::firstOrFail();
        Storage::disk('local')->put('game-builds/module', 'INERT DLL FIXTURE');
        $game->update(['dll_path' => 'game-builds/module', 'dll_name' => 'module.dll', 'dll_sha256' => hash('sha256', 'INERT DLL FIXTURE')]);
        [$license,$key] = app(LicenseService::class)->issue(['label' => 'DLL test', 'days' => 1], null, null);
        $headers = ['Authorization' => 'Bearer '.$key, 'X-HWID' => 'DEVICE-TEST-001', 'X-Game' => $game->code];
        $this->getJson('/api/v1/games/dll')->assertUnprocessable();
        $this->getJson('/api/v1/games/dll', $headers)->assertStatus(409)->assertJsonPath('code', 'activation_required');
        $this->assertNull($license->fresh()->activated_at);
        app(LicenseService::class)->check($key, 'DEVICE-TEST-001', true, null, $game->code);
        $this->get('/api/v1/games/dll', $headers)->assertOk()->assertDownload('module.dll')->assertHeader('X-Checksum-SHA256', hash('sha256', 'INERT DLL FIXTURE'));
        $this->getJson('/api/v1/games/dll', array_replace($headers, ['X-HWID' => 'DEVICE-OTHER-002']))->assertForbidden()->assertJsonPath('code', 'hwid_mismatch');
        $other = Game::factory()->create();
        $this->getJson('/api/v1/games/dll', array_replace($headers, ['X-Game' => $other->code]))->assertForbidden()->assertJsonPath('code', 'game_not_allowed');
        $license->update(['expires_at' => now()]);
        $this->getJson('/api/v1/games/dll', $headers)->assertForbidden()->assertJsonPath('code', 'expired');
        $this->assertDatabaseHas('license_events', ['action' => 'downloaded_dll', 'license_key_id' => $license->id]);
    }

    public function test_member_page_and_direct_web_route_never_offer_dll(): void
    {
        [$license] = app(LicenseService::class)->issue(['label' => 'Member', 'days' => 1], null, null);
        $this->withSession(['member_license_id' => $license->id])->get('/member')->assertOk()->assertDontSee('Unduh DLL');
        $this->get('/member/games/'.Game::firstOrFail()->id.'/downloads/dll')->assertNotFound();
    }
}
