<?php

namespace Tests\Feature;

use App\Models\FinancialTransaction;
use App\Models\LicenseKey;
use App\Models\LicenseProduct;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Services\FinanceReportService;
use App\Services\LicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PurchaseFlowTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): LicenseKey
    {
        return app(LicenseService::class)->issue(['label' => 'Key label', 'member_name' => 'Nama Member', 'days' => 1], null, null)[0];
    }

    public function test_member_can_order_server_priced_package_once_per_token_and_track_only_own_orders(): void
    {
        $owner = $this->owner();
        $product = LicenseProduct::factory()->create();
        $token = (string) Str::uuid();
        $this->withSession(['member_license_id' => $owner->id])->get('/member/shop')->assertOk()->assertSee($product->name);
        $payload = ['product_id' => $product->id, 'idempotency_key' => $token, 'amount' => 1];
        $this->post('/member/orders', $payload)->assertRedirect('/member/shop')->assertSessionHasNoErrors();
        $this->post('/member/orders', $payload)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('purchase_orders', 1);
        $this->assertDatabaseCount('financial_transactions', 0);
        $order = PurchaseOrder::firstOrFail();
        $this->assertSame(50000, $order->amount);
        $this->assertSame('pending', $order->status);
        PurchaseOrder::factory()->create(['number' => 'SECRET-OTHER-MEMBER']);
        $this->get('/member/shop')->assertSee($order->number)->assertDontSee('SECRET-OTHER-MEMBER');
    }

    public function test_confirmed_order_issues_exactly_one_key_and_one_matching_ledger_entry(): void
    {
        $owner = $this->owner();
        $product = LicenseProduct::factory()->create();
        $this->withSession(['member_license_id' => $owner->id])->post('/member/orders', ['product_id' => $product->id, 'idempotency_key' => (string) Str::uuid()]);
        $order = PurchaseOrder::firstOrFail();
        $admin = User::factory()->create();
        $this->actingAs($admin)->put('/products/'.$product->id, ['name' => 'Updated package', 'price' => 999999, 'days' => 30, 'game_ids' => $product->game_ids])->assertSessionHasNoErrors();
        $admin = User::factory()->create();
        $this->actingAs($admin)->post('/orders/'.$order->id.'/settle', ['payment_reference' => 'BANK-SETTLE-01', 'payment_method' => 'bank_transfer', 'payment_provider' => 'Test Bank', 'payer_name' => 'Test Buyer', 'paid_at' => now('Asia/Jakarta')->format('Y-m-d\TH:i')])->assertSessionHasNoErrors();
        $this->post('/orders/'.$order->id.'/settle', ['payment_reference' => 'BANK-SETTLE-01', 'payment_method' => 'bank_transfer', 'payment_provider' => 'Test Bank', 'payer_name' => 'Test Buyer', 'paid_at' => now('Asia/Jakarta')->format('Y-m-d\TH:i')])->assertSessionHasNoErrors();
        $order->refresh();
        $this->assertSame('paid', $order->status);
        $this->assertDatabaseCount('license_keys', 2);
        $this->assertDatabaseCount('financial_transactions', 1);
        $ledger = FinancialTransaction::firstOrFail();
        $this->assertSame($order->amount, $ledger->amount);
        $this->assertSame(50000, $ledger->amount);
        $this->assertSame($order->id, $ledger->order_id);
        $key = $order->issuedLicense;
        $this->assertNull($key->activated_at);
        $this->assertNull($key->expires_at);
        $this->assertSame(604800, $key->duration_seconds);
        $this->assertSame('Nama Member', $key->member_name);
        $this->assertEquals($order->game_ids, $key->games->modelKeys());
        $r = app(FinanceReportService::class)->report(now('Asia/Jakarta')->toDateString(), now('Asia/Jakarta')->toDateString());
        $this->assertSame(50000, $r['income']);
        $this->assertSame($r['income'], $r['daily']->sum('income'));
        $this->withSession(['member_license_id' => $owner->id])->get('/member/shop')->assertSee($key->key_value);
        $this->post('/finance/'.$ledger->id.'/void')->assertSessionHasErrors('transaction');
        $this->assertSame('posted', $ledger->fresh()->status);
    }

    public function test_duplicate_payment_reference_rolls_back_without_issuing_extra_key(): void
    {
        $order = PurchaseOrder::factory()->create();
        FinancialTransaction::factory()->create(['external_reference' => 'USED']);
        $count = LicenseKey::count();
        $this->actingAs(User::factory()->create())->post('/orders/'.$order->id.'/settle', ['payment_reference' => 'USED', 'payment_method' => 'bank_transfer', 'payment_provider' => 'Test Bank', 'payer_name' => 'Test Buyer', 'paid_at' => now('Asia/Jakarta')->format('Y-m-d\TH:i')])->assertSessionHasErrors('payment_reference');
        $this->assertSame($count, LicenseKey::count());
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_cancelled_order_has_no_revenue_and_cannot_be_settled(): void
    {
        $order = PurchaseOrder::factory()->create();
        $this->actingAs(User::factory()->create())->post('/orders/'.$order->id.'/cancel')->assertSessionHasNoErrors();
        $this->post('/orders/'.$order->id.'/settle', ['payment_reference' => 'NO', 'payment_method' => 'bank_transfer', 'payment_provider' => 'Test Bank', 'payer_name' => 'Test Buyer', 'paid_at' => now('Asia/Jakarta')->format('Y-m-d\TH:i')])->assertSessionHasErrors('order');
        $this->assertDatabaseCount('financial_transactions', 0);
    }

    public function test_disabled_products_and_foreign_idempotency_tokens_are_rejected(): void
    {
        $owner = $this->owner();
        $product = LicenseProduct::factory()->create(['is_active' => false]);
        $this->withSession(['member_license_id' => $owner->id])->post('/member/orders', ['product_id' => $product->id, 'idempotency_key' => (string) Str::uuid()])->assertSessionHasErrors('product');
        $foreign = PurchaseOrder::factory()->create();
        $product->update(['is_active' => true]);
        $this->post('/member/orders', ['product_id' => $product->id, 'idempotency_key' => $foreign->idempotency_key])->assertForbidden();
    }

    public function test_members_cannot_settle_or_manage_catalog_and_admin_views_render(): void
    {
        $owner = $this->owner();
        $order = PurchaseOrder::factory()->create();
        $this->withSession(['member_license_id' => $owner->id])->post('/orders/'.$order->id.'/settle', ['payment_reference' => 'ATTACK', 'payment_method' => 'bank_transfer', 'payment_provider' => 'Test Bank', 'payer_name' => 'Test Buyer', 'paid_at' => now('Asia/Jakarta')->format('Y-m-d\TH:i')])->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/orders')->assertOk()->assertSee($order->number);
    }

    public function test_member_name_and_key_label_remain_separate_and_key_is_at_top(): void
    {
        $owner = $this->owner();
        $this->assertSame('Key label', $owner->label);
        $this->assertSame('Nama Member', $owner->member_name);
        $response = $this->withSession(['member_license_id' => $owner->id])->get('/member')->assertOk()->assertSee('Halo, Nama Member.');
        $html = $response->getContent();
        $this->assertLessThan(strpos($html, 'Durasi realtime'), strpos($html, 'License key Anda'));
    }

    public function test_member_can_cancel_only_own_pending_order(): void
    {
        $owner = $this->owner();
        $mine = PurchaseOrder::factory()->create(['owner_license_id' => $owner->id]);
        $other = PurchaseOrder::factory()->create();
        $this->withSession(['member_license_id' => $owner->id])->post('/member/orders/'.$other->id.'/cancel')->assertForbidden();
        $this->post('/member/orders/'.$mine->id.'/cancel')->assertSessionHasNoErrors()->assertRedirect('/member/shop');
        $this->assertSame('cancelled', $mine->fresh()->status);
        $this->assertDatabaseCount('financial_transactions', 0);
    }
}
