<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GameDownloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private array $uploadPaths = [];

    private function upload(string $name): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'kazuha-upload-');
        file_put_contents($path, 'Inert upload fixture, never executable.');
        $this->uploadPaths[] = $path;

        return new UploadedFile($path, $name, 'application/octet-stream', null, true);
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

    private function release(): Game
    {
        $game = Game::firstOrFail();
        Storage::disk('local')->put('game-builds/test-injector', 'inert-test-injector');
        Storage::disk('local')->put('game-builds/test-dll', 'inert-test-dll');
        $game->update(['injector_path' => 'game-builds/test-injector', 'injector_name' => 'rules-of-survival-injector.exe', 'injector_sha256' => hash('sha256', 'inert-test-injector'), 'injector_size' => 19, 'dll_path' => 'game-builds/test-dll', 'dll_name' => 'rules-of-survival-dll.dll', 'dll_size' => 13]);

        return $game;
    }

    private function license(): array
    {
        return app(LicenseService::class)->issue(['label' => 'Download member', 'days' => 1], null, null);
    }

    public function test_pending_member_can_download_assigned_injector_but_not_dll_without_activation(): void
    {
        $game = $this->release();
        [$license] = $this->license();
        $this->withSession(['member_license_id' => $license->id])->get('/member/games/'.$game->id.'/downloads/injector')->assertOk()->assertDownload('rules-of-survival-injector.exe')->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->get('/member/games/'.$game->id.'/downloads/dll')->assertNotFound();
        $this->assertNull($license->fresh()->activated_at);
        $this->assertDatabaseHas('license_events', ['license_key_id' => $license->id, 'action' => 'downloaded_injector']);
        $this->assertDatabaseMissing('license_events', ['license_key_id' => $license->id, 'action' => 'downloaded_dll']);
    }

    public function test_guessing_another_games_download_url_is_forbidden(): void
    {
        $game = $this->release();
        $other = Game::factory()->create();
        [$license] = $this->license();
        $license->games()->sync([$other->id]);
        $this->withSession(['member_license_id' => $license->id])->get('/member/games/'.$game->id.'/downloads/injector')->assertForbidden();
        $this->get('/member')->assertDontSee('rules-of-survival-injector.exe')->assertDontSee('Rules Of Survival');
    }

    public function test_expired_revoked_deleted_and_anonymous_keys_cannot_download(): void
    {
        $game = $this->release();
        [$license] = $this->license();
        $url = '/member/games/'.$game->id.'/downloads/injector';
        $this->get($url)->assertRedirect('/member/login');
        $license->update(['activated_at' => now()->subDays(2), 'expires_at' => now()->subDay()]);
        $this->withSession(['member_license_id' => $license->id])->get($url)->assertForbidden();
        $license->update(['revoked_at' => now()]);
        $this->withSession(['member_license_id' => $license->id])->get($url)->assertRedirect('/member/login');
        $license->delete();
        $this->withSession(['member_license_id' => $license->id])->get($url)->assertRedirect('/member/login');
    }

    public function test_download_rechecks_access_after_admin_removes_game(): void
    {
        $game = $this->release();
        [$license] = $this->license();
        $this->withSession(['member_license_id' => $license->id])->get('/member')->assertSee('Unduh Injector');
        $license->games()->detach();
        $this->get('/member/games/'.$game->id.'/downloads/injector')->assertForbidden();
    }

    public function test_missing_assets_and_invalid_kinds_return_404_and_private_paths_are_not_public(): void
    {
        $game = Game::firstOrFail();
        [$license] = $this->license();
        $this->withSession(['member_license_id' => $license->id])->get('/member/games/'.$game->id.'/downloads/injector')->assertNotFound();
        $this->get('/member/games/'.$game->id.'/downloads/php')->assertNotFound();
        $game = $this->release();
        $this->get('/storage/game-builds/test-injector')->assertNotFound();
        $this->get('/storage/game-builds/test-dll')->assertNotFound();
    }

    public function test_admin_uploads_separate_packages_replaces_and_removes_them_with_audit(): void
    {
        $this->actingAs(User::factory()->create());
        $this->post('/games', ['name' => 'Upload Game', 'code' => 'upload-game', 'release_version' => '1.0', 'injector_file' => $this->upload('build.exe'), 'dll_file' => $this->upload('module.dll')])->assertRedirect('/games')->assertSessionHasNoErrors();
        $game = Game::where('code', 'upload-game')->firstOrFail();
        Storage::disk('local')->assertExists([$game->injector_path, $game->dll_path]);
        $this->assertNotSame($game->injector_path, $game->dll_path);
        $this->assertSame(hash_file('sha256', Storage::disk('local')->path($game->injector_path)), $game->injector_sha256);
        $this->get('/games/'.$game->id.'/downloads/injector')->assertOk()->assertDownload('upload-game-injector.exe');
        $old = $game->injector_path;
        $dll = $game->dll_path;
        $this->put('/games/'.$game->id, ['name' => 'Upload Game', 'injector_file' => $this->upload('next.zip')])->assertSessionHasNoErrors();
        Storage::disk('local')->assertMissing($old);
        Storage::disk('local')->assertExists($dll);
        $game->refresh();
        $new = $game->injector_path;
        $this->put('/games/'.$game->id, ['name' => 'Upload Game', 'remove_injector' => 1])->assertSessionHasNoErrors();
        Storage::disk('local')->assertMissing($new);
        $this->assertNull($game->fresh()->injector_path);
        $this->assertDatabaseHas('license_events', ['action' => 'game_updated']);
    }

    public function test_upload_rejects_unsupported_extensions_and_oversized_files(): void
    {
        $this->actingAs(User::factory()->create());
        $game = Game::firstOrFail();
        $this->put('/games/'.$game->id, ['name' => $game->name, 'injector_file' => UploadedFile::fake()->create('shell.php', 2), 'dll_file' => UploadedFile::fake()->create('module.exe', 2)])->assertSessionHasErrors(['injector_file', 'dll_file']);
        $this->put('/games/'.$game->id, ['name' => $game->name, 'injector_file' => UploadedFile::fake()->create('huge.exe', 20481)])->assertSessionHasErrors('injector_file');
        $this->assertNull($game->fresh()->injector_path);
        $this->assertEmpty(Storage::disk('local')->allFiles());
    }
}
