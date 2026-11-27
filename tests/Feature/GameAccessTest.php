<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\LicenseKey;
use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameAccessTest extends TestCase
{
    use RefreshDatabase;

    private function issue(array $ids = []): array
    {
        return app(LicenseService::class)->issue(['member_name' => 'Test Member', 'label' => 'Multi Game', 'days' => 1, ...($ids ? ['game_ids' => $ids] : [])], null, '127.0.0.1');
    }

    public function test_admin_can_create_key_with_multiple_game_access_and_edit_assignments(): void
    {
        $first = Game::firstOrFail();
        $second = Game::factory()->create();
        $this->actingAs(User::factory()->create());
        $this->post('/licenses', ['member_name' => 'Test Member', 'label' => 'Multi Game', 'days' => 1, 'game_ids' => [$first->id, $second->id]])->assertSessionHasNoErrors();
        $license = LicenseKey::where('label', 'Multi Game')->firstOrFail();
        $this->assertCount(2, $license->games);
        $this->assertNull($license->activated_at);
        $this->put('/licenses/'.$license->id, ['member_name' => 'Test Member', 'label' => 'Edited', 'game_ids' => [$second->id]])->assertSessionHasNoErrors();
        $this->assertEquals([$second->id], $license->fresh()->games->modelKeys());
    }

    public function test_create_requires_at_least_one_existing_game_and_rejects_duplicates(): void
    {
        $this->actingAs(User::factory()->create());
        foreach ([[], [999999], [1, 1]] as $ids) {
            $this->post('/licenses', ['member_name' => 'Test Member', 'label' => 'Bad', 'days' => 1, 'game_ids' => $ids])->assertSessionHasErrors();
        }
        $this->assertDatabaseCount('license_keys', 0);
    }

    public function test_injector_selects_game_and_shared_hwid_and_expiry_apply_across_games(): void
    {
        $this->freezeTime();
        $ros = Game::firstOrFail();
        $other = Game::factory()->create();
        [$license,$key] = $this->issue([$ros->id, $other->id]);
        $this->postJson('/api/v1/licenses/validate', ['key' => $key, 'hwid' => 'DEVICE-AAA-001', 'game_code' => $ros->code])->assertOk()->assertJsonPath('data.game.code', $ros->code);
        $expires = $license->fresh()->expires_at;
        $this->travel(1)->minutes();
        $this->postJson('/api/v1/licenses/validate', ['key' => $key, 'hwid' => 'DEVICE-AAA-001', 'game_code' => $other->code])->assertOk()->assertJsonPath('data.remaining_seconds', 86340);
        $this->assertTrue($license->fresh()->expires_at->equalTo($expires));
        $this->postJson('/api/v1/licenses/validate', ['key' => $key, 'hwid' => 'DEVICE-BBB-002', 'game_code' => $other->code])->assertForbidden()->assertJsonPath('code', 'hwid_mismatch');
    }

    public function test_unauthorized_game_never_binds_or_starts_key(): void
    {
        $other = Game::factory()->create();
        [$license,$key] = $this->issue();
        $this->postJson('/api/v1/licenses/validate', ['key' => $key, 'hwid' => 'DEVICE-AAA-001', 'game_code' => $other->code])->assertForbidden()->assertJsonPath('code', 'game_not_allowed');
        $this->assertNull($license->fresh()->hwid);
        $this->assertNull($license->fresh()->activated_at);
        $this->assertNull($license->fresh()->expires_at);
    }

    public function test_unknown_or_missing_game_is_rejected(): void
    {
        [$license,$key] = $this->issue();
        $this->postJson('/api/v1/licenses/validate', ['key' => $key, 'hwid' => 'DEVICE-AAA-001', 'game_code' => 'unknown-game'])->assertForbidden()->assertJsonPath('code', 'game_unavailable');
        $this->postJson('/api/v1/licenses/validate', ['key' => $key, 'hwid' => 'DEVICE-AAA-001'])->assertUnprocessable()->assertJsonValidationErrors('game_code');
        $this->assertNull($license->fresh()->hwid);
    }

    public function test_get_requires_game_header_and_rejects_unassigned_game(): void
    {
        $other = Game::factory()->create();
        [$license,$key] = $this->issue();
        app(LicenseService::class)->check($key, 'DEVICE-AAA-001', true, null);
        $this->getJson('/api/v1/licenses/status', ['Authorization' => 'Bearer '.$key, 'X-HWID' => 'DEVICE-AAA-001'])->assertUnprocessable()->assertJsonValidationErrors('game_code');
        $this->getJson('/api/v1/licenses/status', ['Authorization' => 'Bearer '.$key, 'X-HWID' => 'DEVICE-AAA-001', 'X-Game' => $other->code])->assertForbidden()->assertJsonPath('code', 'game_not_allowed');
    }

    public function test_member_sees_only_assigned_game_names_and_downloads(): void
    {
        $other = Game::factory()->create(['name' => 'Private Other Game']);
        [$license] = $this->issue();
        $this->withSession(['member_license_id' => $license->id])->get('/member')->assertOk()->assertSee('Rules Of Survival')->assertDontSee('Private Other Game');
        $this->get('/games')->assertRedirect('/login');
    }

    public function test_admin_can_add_and_edit_game_but_code_is_permanent(): void
    {
        $this->actingAs(User::factory()->create());
        $this->post('/games', ['name' => 'New Game', 'code' => 'new-game', 'release_version' => '1.0'])->assertSessionHasNoErrors()->assertRedirect('/games');
        $game = Game::where('code', 'new-game')->firstOrFail();
        $this->put('/games/'.$game->id, ['name' => 'Renamed', 'release_version' => '2.0'])->assertSessionHasNoErrors();
        $this->assertSame('Renamed', $game->fresh()->name);
        $this->put('/games/'.$game->id, ['name' => 'Renamed', 'code' => 'replacement'])->assertSessionHasErrors('code');
        $this->assertSame('new-game', $game->fresh()->code);
        $this->assertDatabaseHas('license_events', ['action' => 'game_created']);
        $this->assertDatabaseHas('license_events', ['action' => 'game_updated']);
    }

    public function test_member_identity_cannot_configure_games(): void
    {
        [$license] = $this->issue();
        $this->withSession(['member_license_id' => $license->id])->post('/games', ['name' => 'Attack', 'code' => 'attack'])->assertRedirect('/login');
        $member = User::factory()->create();
        $member->role = 'member';
        $member->save();
        $this->actingAs($member)->post('/games', ['name' => 'Attack', 'code' => 'attack'])->assertForbidden();
    }
}
