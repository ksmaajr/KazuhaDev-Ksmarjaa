<?php

namespace Tests\Feature;

use App\Models\FinancialTransaction;
use App\Models\LicenseEvent;
use App\Models\LicenseProduct;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecordFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_admin_lists_are_bounded_and_reject_unlimited_page_sizes(): void
    {
        $this->actingAs(User::factory()->create());
        foreach (['/licenses', '/products', '/orders', '/finance', '/activity', '/admins', '/games'] as $path) {
            $this->get($path.'?per_page=50')->assertOk();
            $this->getJson($path.'?per_page=10000')->assertUnprocessable();
        }
    }

    public function test_catalog_and_orders_are_separate_and_all_detail_endpoints_render(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);
        $product = LicenseProduct::factory()->create(['name' => 'Package record']);
        $order = PurchaseOrder::factory()->create(['product_name' => 'ORDER SNAPSHOT']);
        $ledger = FinancialTransaction::factory()->create();
        [$license,$key] = app(LicenseService::class)->issue(['label' => 'Detail Key', 'days' => 1], null, null);
        $event = LicenseEvent::where('license_key_id', $license->id)->firstOrFail();
        $this->get('/products')->assertOk()->assertSee('Package record')->assertDontSee('ORDER SNAPSHOT');
        $this->get('/orders')->assertOk()->assertSee($order->number)->assertDontSee('Tambah paket lisensi');
        foreach (['license' => $license->id, 'event' => $event->id, 'product' => $product->id, 'order' => $order->id, 'transaction' => $ledger->id, 'admin' => $admin->id] as $kind => $id) {
            $this->getJson('/records/'.$kind.'/'.$id)->assertOk()->assertJsonStructure(['title', 'rows', 'links', 'timeline'])->assertDontSee($key)->assertDontSee('password')->assertDontSee('key_hash');
        }
    }

    public function test_order_filters_match_member_status_method_and_keep_query_on_pagination(): void
    {
        $this->actingAs(User::factory()->create());
        PurchaseOrder::factory()->count(12)->create(['member_name' => 'Filter Buyer', 'status' => 'pending', 'payment_method' => 'qris']);
        PurchaseOrder::factory()->create(['member_name' => 'Hidden Buyer', 'status' => 'paid', 'payment_method' => 'cash']);
        $response = $this->get('/orders?q=Filter&status=pending&method=qris&per_page=8');
        $response->assertOk()->assertDontSee('Hidden Buyer')->assertViewHas('orders', fn ($orders) => $orders->total() === 12 && $orders->count() === 8 && str_contains($orders->nextPageUrl(), 'status=pending'));
    }

    public function test_finance_filter_totals_chart_and_export_use_the_same_matching_set(): void
    {
        $this->actingAs(User::factory()->create());
        FinancialTransaction::factory()->create(['amount' => 12500, 'description' => 'Target receipt', 'paid_at' => now()]);
        FinancialTransaction::factory()->create(['amount' => 99999, 'description' => 'Other receipt', 'paid_at' => now()]);
        $this->get('/finance?q=Target&direction=income&min_amount=10000&max_amount=20000')->assertOk()->assertViewHas('report', fn ($report) => $report['income'] === 12500 && $report['daily']->sum('income') === 12500)->assertViewHas('transactions', fn ($rows) => $rows->total() === 1);
        $this->get('/finance/export?q=Target&direction=income&min_amount=10000&max_amount=20000')->assertOk();
    }

    public function test_notification_and_member_details_cannot_cross_member_boundary(): void
    {
        [$mine] = app(LicenseService::class)->issue(['label' => 'Mine', 'days' => 1], null, null);
        [$other] = app(LicenseService::class)->issue(['label' => 'Secret other', 'days' => 1], null, null);
        $ownEvent = LicenseEvent::where('license_key_id', $mine->id)->firstOrFail();
        $foreign = LicenseEvent::where('license_key_id', $other->id)->firstOrFail();
        $order = PurchaseOrder::factory()->create(['owner_license_id' => $other->id]);
        $this->withSession(['member_license_id' => $mine->id])->getJson('/member/notifications')->assertOk()->assertJsonPath('items.0.details_url', route('member.records.show', ['event', $ownEvent->id]));
        $this->getJson('/member/records/event/'.$ownEvent->id)->assertOk();
        $this->getJson('/member/records/event/'.$foreign->id)->assertNotFound();
        $this->getJson('/member/records/order/'.$order->id)->assertNotFound();
        $this->getJson('/member/records/license/'.$other->id)->assertNotFound();
    }

    public function test_homepage_and_member_login_offer_purchase_but_dashboard_focuses_on_renewal(): void
    {
        $this->get('/')->assertOk();
        $this->get('/shop')->assertOk();
        $this->get('/member/login')->assertOk()->assertSee('Lihat paket lisensi');
        [$license] = app(LicenseService::class)->issue(['label' => 'Member', 'days' => 1], null, null);
        $this->withSession(['member_license_id' => $license->id])->get('/member')->assertOk()->assertSee('Pilih paket perpanjangan')->assertDontSee('Beli key');
        $this->get('/member/renew')->assertOk();
    }
}
