<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DllVariantTest extends TestCase
{
    use RefreshDatabase;

    private array $uploadPaths = [];

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    protected function tearDown(): void
    {
        foreach ($this->uploadPaths as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
        parent::tearDown();
    }

    private function upload(string $name, string $content = 'INERT FIXTURE'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'kz-variant-');
        file_put_contents($path, $content);
        $this->uploadPaths[] = $path;

        return new UploadedFile($path, $name, 'application/octet-stream', null, true);
    }

    private function variants(): Game
    {
        $game = Game::firstOrFail();
        $this->actingAs(User::factory()->create())->put('/games/'.$game->id, [
            'name' => $game->name,
            'dll_variants' => [
                ['code' => 'infinity', 'name' => 'ROS Infinity', 'file' => $this->upload('infinity.dll', 'INFINITY FIXTURE')],
                ['code' => 'mirae', 'name' => 'ROS Mirae', 'file' => $this->upload('mirae.dll', 'MIRAE FIXTURE')],
            ],
        ])->assertSessionHasNoErrors()->assertRedirect('/games');

        return $game->fresh();
    }

    private function access(Game $game): array
    {
        [$license, $key] = app(LicenseService::class)->issue(['label' => 'QA', 'member_name' => 'QA Member', 'days' => 2, 'game_ids' => [$game->id]], null, null);
        app(LicenseService::class)->check($key, 'QA-DEVICE-001', true, null, $game->code);

        return [$license, $key, ['Authorization' => 'Bearer '.$key, 'X-HWID' => 'QA-DEVICE-001', 'X-Game' => $game->code]];
    }

    public function test_admin_uploads_replaces_and_removes_independent_private_variants(): void
    {
        $game = $this->variants();
        $variants = $game->dll_variants;
        $this->assertCount(2, $variants);
        Storage::disk('local')->assertExists([$variants['infinity']['path'], $variants['mirae']['path']]);
        $this->get('/games')->assertOk()->assertSee('ROS Infinity')->assertSee('ROS Mirae')->assertSee('Tambah versi game');
        $this->assertArrayNotHasKey('dll_variants', $game->toArray());
        $this->put('/games/'.$game->id, ['name' => $game->name, 'dll_variants' => [
            ['code' => 'infinity', 'name' => 'Infinity Next', 'file' => $this->upload('new.dll', 'NEXT FIXTURE')],
            ['code' => 'mirae', 'name' => 'ROS Mirae'],
        ]])->assertSessionHasNoErrors();
        Storage::disk('local')->assertMissing($variants['infinity']['path']);
        Storage::disk('local')->assertExists($variants['mirae']['path']);
        $this->assertSame('Infinity Next', $game->fresh()->dll_variants['infinity']['name']);
        $this->put('/games/'.$game->id, ['name' => $game->name, 'dll_variants' => [['code' => 'mirae', 'name' => 'ROS Mirae', 'remove' => 1]]])->assertSessionHasNoErrors();
        Storage::disk('local')->assertMissing($variants['mirae']['path']);
        $this->assertArrayNotHasKey('mirae', $game->fresh()->dll_variants);
        $this->assertArrayHasKey('infinity', $game->fresh()->dll_variants);
    }

    public function test_api_selects_exact_variant_and_never_falls_back(): void
    {
        $game = $this->variants();
        [, , $headers] = $this->access($game);
        foreach (['infinity' => 'INFINITY FIXTURE', 'mirae' => 'MIRAE FIXTURE'] as $code => $bytes) {
            $this->get('/api/v1/games/dll', [...$headers, 'X-Game-Version' => $code])->assertOk()
                ->assertDownload($game->code.'-'.$code.'.dll')->assertHeader('X-Game-Version', $code)
                ->assertHeader('X-Checksum-SHA256', hash('sha256', $bytes));
        }
        $this->getJson('/api/v1/games/dll', $headers)->assertStatus(422)->assertJsonPath('code', 'game_version_required');
        $this->getJson('/api/v1/games/dll', [...$headers, 'X-Game-Version' => 'unknown'])->assertNotFound()->assertJsonPath('code', 'release_unavailable');
        $this->getJson('/api/v1/games/dll', [...$headers, 'X-Game-Version' => '../mirae'])->assertUnprocessable();
        Storage::disk('local')->delete($game->dll_variants['infinity']['path']);
        $this->getJson('/api/v1/games/dll', [...$headers, 'X-Game-Version' => 'infinity'])->assertNotFound();
    }

    public function test_variant_selection_does_not_bypass_hwid_game_or_expiry(): void
    {
        $game = $this->variants();
        [$license, , $headers] = $this->access($game);
        $headers['X-Game-Version'] = 'infinity';
        $this->getJson('/api/v1/games/dll', [...$headers, 'X-HWID' => 'QA-OTHER-PC'])->assertForbidden()->assertJsonPath('code', 'hwid_mismatch');
        $other = Game::factory()->create();
        $this->getJson('/api/v1/games/dll', [...$headers, 'X-Game' => $other->code])->assertForbidden()->assertJsonPath('code', 'game_not_allowed');
        $license->update(['expires_at' => now()]);
        $this->getJson('/api/v1/games/dll', $headers)->assertForbidden()->assertJsonPath('code', 'expired');
    }

    public function test_invalid_variant_uploads_are_rejected_and_rollback_preserves_files(): void
    {
        $game = $this->variants();
        $original = $game->dll_variants;
        $this->put('/games/'.$game->id, ['name' => $game->name, 'dll_variants' => [
            ['code' => 'same', 'name' => 'One', 'file' => $this->upload('one.dll')],
            ['code' => 'same', 'name' => 'Two', 'file' => $this->upload('two.dll')],
        ]])->assertSessionHasErrors('dll_variants.0.code');
        $this->put('/games/'.$game->id, ['name' => $game->name, 'dll_variants' => [
            ['code' => 'infinity', 'name' => 'Bad', 'file' => $this->upload('bad.exe')],
        ]])->assertSessionHasErrors('dll_variants.0.file');
        $this->put('/games/'.$game->id, ['name' => $game->name, 'dll_variants' => [
            ['code' => 'infinity', 'name' => 'Too big', 'file' => UploadedFile::fake()->create('huge.dll', 20481)],
        ]])->assertSessionHasErrors('dll_variants.0.file');
        $this->put('/games/'.$game->id, ['name' => 'Should rollback', 'dll_variants' => [
            ['code' => 'infinity', 'name' => 'Replaced', 'file' => $this->upload('next.dll')],
            ['code' => 'missing', 'name' => 'No file'],
        ]])->assertSessionHasErrors('dll_variants.1.file');
        $this->assertSame($game->name, $game->fresh()->name);
        $this->assertSame($original, $game->fresh()->dll_variants);
        $this->assertCount(2, Storage::disk('local')->allFiles());
    }

    public function test_member_cannot_modify_variants(): void
    {
        $game = Game::firstOrFail();
        $user = User::factory()->create();
        $user->role = 'member';
        $user->save();
        $this->actingAs($user)->put('/games/'.$game->id, ['name' => $game->name, 'dll_variants' => [['code' => 'mirae', 'name' => 'Mirae', 'file' => $this->upload('mirae.dll')]]])->assertForbidden();
        $this->assertEmpty(Storage::disk('local')->allFiles());
    }

    public function test_account_metadata_uses_own_latest_paid_package_without_exposing_key_or_private_paths(): void
    {
        $game = $this->variants();
        [$license, $key, $headers] = $this->access($game);
        PurchaseOrder::factory()->create(['owner_license_id' => $license->id, 'issued_license_id' => $license->id, 'product_name' => 'Original', 'status' => 'paid', 'paid_at' => now()->subDay(), 'duration_seconds' => 86400]);
        PurchaseOrder::factory()->create(['owner_license_id' => $license->id, 'issued_license_id' => $license->id, 'product_name' => 'Renewed package', 'status' => 'paid', 'paid_at' => now(), 'duration_seconds' => 172800]);
        PurchaseOrder::factory()->create(['owner_license_id' => $license->id, 'issued_license_id' => $license->id, 'product_name' => 'Sandbox ignored', 'status' => 'paid', 'gateway_mode' => 'sandbox', 'paid_at' => now()->addMinute()]);
        PurchaseOrder::factory()->create(['owner_license_id' => $license->id, 'issued_license_id' => $license->id, 'product_name' => 'Pending ignored', 'status' => 'pending']);
        $this->getJson('/api/v1/licenses/status', $headers)->assertOk()->assertJsonPath('data.account.member_name', 'QA Member')
            ->assertJsonPath('data.account.key_hint', $license->key_hint)->assertJsonPath('data.account.package.name', 'Renewed package')
            ->assertJsonPath('data.account.package.duration_seconds', 172800)->assertJsonPath('data.account.games.0.code', $game->code)
            ->assertJsonPath('data.expires_at', $license->fresh()->expires_at->toIso8601String())
            ->assertDontSee($key)->assertDontSee($game->dll_variants['infinity']['path']);
        $this->getJson('/api/v1/licenses/status', [...$headers, 'X-HWID' => 'OTHER-DEVICE'])->assertForbidden()->assertJsonPath('data', null);
        $this->postJson('/api/v1/licenses/validate', ['key' => $key, 'hwid' => 'QA-DEVICE-001', 'game_code' => $game->code])->assertOk()->assertJsonPath('data.account.package.name', 'Renewed package');
    }

    public function test_manual_key_has_no_fabricated_package_and_expired_key_gets_no_account_data(): void
    {
        $game = Game::firstOrFail();
        [$license, , $headers] = $this->access($game);
        $this->getJson('/api/v1/licenses/status', $headers)->assertOk()->assertJsonPath('data.account.package', null);
        $license->update(['expires_at' => now()]);
        $this->getJson('/api/v1/licenses/status', $headers)->assertForbidden()->assertJsonPath('code', 'expired')->assertJsonPath('data', null);
    }
}
