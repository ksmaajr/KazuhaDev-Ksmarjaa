<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\LicenseKey;
use App\Models\LicenseProduct;
use App\Models\User;
use App\Services\LicenseService;
use App\Services\PurchaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LicenseDurationTest extends TestCase
{
    use RefreshDatabase;

    private function payload(): array
    {
        return ['member_name' => 'Duration Owner', 'label' => 'Precise duration', 'game_ids' => [Game::firstOrFail()->id], 'hours' => 2, 'minutes' => 35];
    }

    public function test_hours_and_minutes_are_stored_exactly_and_start_only_on_activation(): void
    {
        $this->freezeTime();
        $this->actingAs(User::factory()->create());
        $this->post('/licenses', $this->payload())->assertSessionHasNoErrors();
        $license = LicenseKey::firstOrFail();
        $this->assertSame(9300, $license->duration_seconds);
        $this->assertNull($license->expires_at);
        app(LicenseService::class)->check($license->key_value, 'PRECISE-DEVICE-001', true, '127.0.0.1');
        $this->assertSame(now()->addSeconds(9300)->timestamp, $license->fresh()->expires_at->timestamp);
    }

    public function test_invalid_or_conflicting_duration_parts_are_rejected_without_issuing_a_key(): void
    {
        $this->actingAs(User::factory()->create());
        foreach ([['hours' => 0, 'minutes' => 0], ['hours' => 720, 'minutes' => 1], ['hours' => 721, 'minutes' => 0], ['hours' => 1, 'minutes' => 60], ['hours' => -1, 'minutes' => 30], ['hours' => 1.5, 'minutes' => 0], ['hours' => 1, 'minutes' => 0, 'days' => 1], ['hours' => 1, 'minutes' => null]] as $parts) {
            $this->post('/licenses', array_replace($this->payload(), $parts))->assertSessionHasErrors();
        }
        $this->assertDatabaseCount('license_keys', 0);
        foreach ([['hours' => 0, 'minutes' => 1], ['hours' => 720, 'minutes' => 0]] as $parts) {
            $this->post('/licenses', array_replace($this->payload(), $parts))->assertSessionHasNoErrors();
        }
        $this->assertDatabaseCount('license_keys', 2);
    }

    public function test_pending_edit_and_extension_preserve_minutes_and_hwid(): void
    {
        $this->actingAs(User::factory()->create());
        [$license,$key] = app(LicenseService::class)->issue(['label' => 'Test', 'days' => 1], null, null);
        $this->put('/licenses/'.$license->id, ['label' => 'Updated', 'hours' => 0, 'minutes' => 45])->assertSessionHasNoErrors();
        $this->assertSame(2700, $license->fresh()->duration_seconds);
        app(LicenseService::class)->check($key, 'DURATION-DEVICE-001', true, null);
        $old = $license->fresh()->expires_at;
        $this->put('/licenses/'.$license->id, ['label' => 'Rejected', 'hours' => 1, 'minutes' => 0])->assertSessionHasErrors('hours');
        $this->post('/licenses/'.$license->id.'/extend', ['hours' => 1, 'minutes' => 15])->assertSessionHasNoErrors();
        $license->refresh();
        $this->assertSame($old->addSeconds(4500)->timestamp, $license->expires_at->timestamp);
        $this->assertSame('DURATION-DEVICE-001', $license->hwid);
    }

    public function test_package_duration_snapshot_survives_catalog_changes_and_settlement(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);
        $this->post('/products', ['name' => 'Precision package', 'price' => 10000, 'hours' => 0, 'minutes' => 35, 'game_ids' => [Game::firstOrFail()->id]])->assertSessionHasNoErrors();
        $product = LicenseProduct::firstOrFail();
        $this->assertSame(2100, $product->duration_seconds);
        [$owner] = app(LicenseService::class)->issue(['label' => 'Buyer', 'days' => 1], null, null);
        $order = app(PurchaseService::class)->order($owner, $product->id, (string) Str::uuid(), null);
        $this->put('/products/'.$product->id, ['name' => 'Changed', 'price' => 20000, 'hours' => 10, 'minutes' => 5, 'game_ids' => $product->game_ids])->assertSessionHasNoErrors();
        $settled = app(PurchaseService::class)->settle($order, $admin->id, 'PRECISE-PAYMENT', null);
        $this->assertSame(2100, $settled->issuedLicense->duration_seconds);
        $this->assertSame(10000,$settled->amount);
        $this->assertNotEmpty($settled->game_snapshot);
    }
}
