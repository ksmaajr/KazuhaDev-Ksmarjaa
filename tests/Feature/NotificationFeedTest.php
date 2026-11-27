<?php

namespace Tests\Feature;

use App\Models\LicenseEvent;
use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_feed_returns_business_events_and_incremental_updates_without_secrets(): void
    {
        $this->actingAs(User::factory()->create());
        $first = $this->getJson('/notifications')->assertOk();
        $last = $first->json('latest_id');
        [$license,$key] = app(LicenseService::class)->issue(['label' => 'New key', 'days' => 1], null, null);
        $response = $this->getJson('/notifications?after='.$last)->assertOk()->assertJsonPath('new_items.0.title', 'Key baru dibuat');
        $response->assertDontSee($key)->assertDontSee('key_hash')->assertDontSee('password');
        $this->assertNotSame($first->json('revision'), $response->json('revision'));
    }

    public function test_member_feed_cannot_see_other_members_or_admin_financial_events(): void
    {
        [$mine] = app(LicenseService::class)->issue(['label' => 'Mine', 'days' => 1], null, null);
        [$other] = app(LicenseService::class)->issue(['label' => 'SECRET OTHER', 'days' => 1], null, null);
        LicenseEvent::create(['action' => 'finance_recorded', 'metadata' => ['reference' => 'SECRET FINANCE']]);
        $this->withSession(['member_license_id' => $mine->id])->getJson('/member/notifications')->assertOk()->assertSee('Mine')->assertDontSee('SECRET OTHER')->assertDontSee('SECRET FINANCE');
        $this->getJson('/notifications')->assertUnauthorized();
        $this->getJson('/member/notifications?after=-1')->assertUnprocessable();
    }

    public function test_revoked_member_session_loses_realtime_feed_access(): void
    {
        [$mine] = app(LicenseService::class)->issue(['label' => 'Mine', 'days' => 1], null, null);
        $mine->update(['revoked_at' => now()]);
        $this->withSession(['member_license_id' => $mine->id])->getJson('/member/notifications')->assertUnauthorized();
    }
}
