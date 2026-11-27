<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GameArtworkTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_uploads_replaces_and_removes_banner_and_logo_and_default_ros_artworks_render(): void
    {
        Storage::fake('local');
        $game = Game::firstOrFail();
        $this->get('/game-artwork/'.$game->id.'/banner')->assertOk();
        $this->get('/game-artwork/'.$game->id.'/logo')->assertOk();
        $path = tempnam(sys_get_temp_dir(), 'kazuha-image-');
        file_put_contents($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a9uoAAAAASUVORK5CYII='));
        try {
            $this->actingAs(User::factory()->create())->put('/games/'.$game->id, ['name' => $game->name, 'banner_file' => new UploadedFile($path, 'banner.png', 'image/png', null, true), 'logo_file' => new UploadedFile($path, 'logo.png', 'image/png', null, true)])->assertSessionHasNoErrors();
            $game->refresh();
            $old = $game->banner_path;
            $logo = $game->logo_path;
            Storage::disk('local')->assertExists([$old, $logo]);
            $this->get('/games')->assertOk()->assertSee('Background / banner game');
            $this->get('/game-artwork/'.$game->id.'/banner')->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
            $this->put('/games/'.$game->id, ['name' => $game->name, 'remove_banner' => 1, 'remove_logo' => 1])->assertSessionHasNoErrors();
            Storage::disk('local')->assertMissing([$old, $logo]);
            $this->assertNull($game->fresh()->banner_path);
            $this->get('/game-artwork/'.$game->id.'/dll')->assertNotFound();
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function test_custom_feedback_components_exist_on_admin_and_member_logins(): void
    {
        $this->get('/login')->assertOk()->assertSee('id="universal-confirm"', false)->assertSee('id="notification-center"', false);
        $this->get('/member/login')->assertOk()->assertSee('id="universal-confirm"', false)->assertDontSee('Administrator? Masuk di sini');
    }
}
