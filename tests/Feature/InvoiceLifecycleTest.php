<?php

namespace Tests\Feature;

use App\Models\FinancialTransaction;
use App\Models\LicenseKey;
use App\Models\LicenseProduct;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Services\LicenseService;
use App\Services\PurchaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class InvoiceLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_uses_invoice_number_for_receipt_status_and_payment_without_exposing_numeric_id(): void
    {
        $order = PurchaseOrder::factory()->create(['owner_license_id' => null, 'guest_token_hash' => hash('sha256', 'invoice-owner')]);
        $this->assertSame(url('/checkout/'.$order->number), route('checkout.receipt', $order));
        $this->assertSame(url('/checkout/'.$order->number.'/status'), route('checkout.status', $order));
        $this->assertSame(url('/checkout/'.$order->number.'/payment'), route('checkout.payment', $order));
        $this->withSession(['checkout_secret' => 'invoice-owner'])->get('/checkout/'.$order->number)->assertOk()->assertSee($order->number)->assertDontSee('Hapus invoice')->assertDontSee('dihapus otomatis setelah 24 jam');
        $this->get('/checkout/'.$order->number.'/status')->assertOk()->assertJsonPath('status', 'pending');
        $this->get('/checkout/'.$order->id)->assertNotFound();
        $this->withSession(['checkout_secret' => 'someone-else'])->get('/checkout/'.$order->number)->assertNotFound();
        $this->get('/checkout/'.$order->number.'/status')->assertNotFound();
    }

    public function test_new_checkout_redirects_to_its_invoice_number(): void
    {
        $product = LicenseProduct::factory()->create();
        $response = $this->post('/checkout', ['product_id' => $product->id, 'idempotency_key' => (string) Str::uuid(), 'member_name' => 'Invoice Buyer', 'customer_contact' => 'buyer@example.test']);
        $order = PurchaseOrder::firstOrFail();
        $response->assertRedirect('/checkout/'.$order->number);
        $this->get('/checkout/'.$order->number)->assertOk()->assertSee('Invoice Buyer');
    }

    public function test_guest_cannot_delete_invoice_even_with_own_checkout_session(): void
    {
        $order = PurchaseOrder::factory()->create(['owner_license_id' => null, 'guest_token_hash' => hash('sha256', 'guest-invoice')]);
        $this->withSession(['checkout_secret' => 'guest-invoice'])->delete('/checkout/'.$order->number)->assertStatus(405);
        $this->delete('/orders/'.$order->id)->assertRedirect('/login');
        $this->get('/checkout/'.$order->number)->assertOk()->assertDontSee('Hapus invoice')->assertDontSee('dihapus otomatis setelah 24 jam');
        $this->assertDatabaseHas('purchase_orders', ['id' => $order->id]);
        $this->assertDatabaseCount('financial_transactions', 0);
    }

    public function test_member_cannot_delete_invoice_and_has_no_delete_control(): void
    {
        $owner = LicenseKey::factory()->create();
        $order = PurchaseOrder::factory()->create(['owner_license_id' => $owner->id]);
        $this->withSession(['member_license_id' => $owner->id])->delete('/checkout/'.$order->number)->assertStatus(405);
        $this->get('/checkout/'.$order->number)->assertOk()->assertDontSee('Hapus invoice')->assertDontSee('dihapus otomatis setelah 24 jam');
        $this->get('/member/shop')->assertOk()->assertDontSee('Hapus invoice');
        $this->assertDatabaseHas('purchase_orders', ['id' => $order->id]);
    }

    public function test_gateway_invoices_paid_invoices_and_financial_records_cannot_be_deleted(): void
    {
        $this->withoutMiddleware(ThrottleRequests::class);
        $this->actingAs(User::factory()->create());
        foreach ([['gateway_status' => 'creating'], ['gateway_status' => 'uncertain'], ['gateway_status' => 'pending'], ['gateway_status' => 'failed'], ['gateway_status' => 'expired'], ['gateway_payment_id' => 'provider-id'], ['gateway_payment_url' => 'https://pay.sumopod.com/existing'], ['status' => 'paid'], ['status' => 'cancelled'], ['paid_at' => now()], ['payment_reference' => 'already-recorded'], ['issued_license_id' => LicenseKey::factory()->create()->id]] as $state) {
            $order = PurchaseOrder::factory()->create($state);
            $this->delete('/orders/'.$order->id)->assertSessionHasErrors('order');
            $this->assertDatabaseHas('purchase_orders', ['id' => $order->id]);
        }
        $order = PurchaseOrder::factory()->create();
        FinancialTransaction::factory()->create(['order_id' => $order->id]);
        $this->delete('/orders/'.$order->id)->assertSessionHasErrors('order');
        $this->assertDatabaseHas('purchase_orders', ['id' => $order->id]);
        $this->assertDatabaseCount('financial_transactions', 1);
    }

    public function test_cleanup_only_deletes_untouched_invoices_after_full_24_hours(): void
    {
        $this->freezeTime();
        $older = PurchaseOrder::factory()->create(['created_at' => now()->subHours(25)]);
        $boundary = PurchaseOrder::factory()->create(['created_at' => now()->subDay()]);
        $recent = PurchaseOrder::factory()->create(['created_at' => now()->subDay()->addSecond()]);
        $protected = [];
        foreach ([['gateway_status' => 'creating'], ['gateway_status' => 'uncertain'], ['gateway_status' => 'pending'], ['gateway_status' => 'expired'], ['gateway_payment_id' => 'provider-older'], ['status' => 'paid'], ['status' => 'cancelled']] as $state) {
            $protected[] = PurchaseOrder::factory()->create($state + ['created_at' => now()->subDays(3)]);
        }
        $ledgerOrder = PurchaseOrder::factory()->create(['created_at' => now()->subDays(3)]);
        FinancialTransaction::factory()->create(['order_id' => $ledgerOrder->id]);
        $this->artisan('invoices:prune', ['--dry-run' => true])->expectsOutput('2 invoice memenuhi syarat pembersihan.')->assertSuccessful();
        $this->assertDatabaseHas('purchase_orders', ['id' => $older->id]);
        $this->artisan('invoices:prune')->expectsOutput('2 invoice belum dibayar dihapus.')->assertSuccessful();
        $this->assertDatabaseMissing('purchase_orders', ['id' => $older->id]);
        $this->assertDatabaseMissing('purchase_orders', ['id' => $boundary->id]);
        foreach (array_merge([$recent, $ledgerOrder], $protected) as $order) {
            $this->assertDatabaseHas('purchase_orders', ['id' => $order->id]);
        }
        $this->assertDatabaseCount('financial_transactions', 1);
        $this->assertDatabaseCount('license_events', 1);
        $this->artisan('invoices:prune')->expectsOutput('0 invoice belum dibayar dihapus.')->assertSuccessful();
    }

    public function test_deletion_rechecks_locked_state_instead_of_using_stale_invoice(): void
    {
        $order = PurchaseOrder::factory()->create();
        PurchaseOrder::whereKey($order->id)->update(['gateway_status' => 'creating']);
        try {
            app(PurchaseService::class)->deleteUnpaid($order, null, null);
            $this->fail('Invoice claimed by gateway must be protected.');
        } catch (ValidationException) {
            $this->assertDatabaseHas('purchase_orders', ['id' => $order->id, 'gateway_status' => 'creating']);
        }
    }

    public function test_admin_can_delete_untouched_invoice_and_other_roles_cannot(): void
    {
        $order = PurchaseOrder::factory()->create();
        $this->delete('/orders/'.$order->id)->assertRedirect('/login');
        $this->actingAs(User::factory()->create(['role' => 'member']))->delete('/orders/'.$order->id)->assertForbidden();
        $this->actingAs(User::factory()->create())->delete('/orders/'.$order->id)->assertRedirect('/orders');
        $this->assertDatabaseMissing('purchase_orders', ['id' => $order->id]);
    }

    public function test_gateway_receipt_has_no_delete_control_and_paid_receipt_displays_issued_key(): void
    {
        $this->actingAs(User::factory()->create());
        $gateway = PurchaseOrder::factory()->create(['gateway_status' => 'uncertain']);
        $this->get('/checkout/'.$gateway->number)->assertOk()->assertDontSee('Hapus invoice')->assertDontSee('dihapus otomatis setelah 24 jam');
        [$license, $key] = app(LicenseService::class)->issue(['label' => 'Paid receipt', 'days' => 1], null, null);
        $paid = PurchaseOrder::factory()->create(['status' => 'paid', 'issued_license_id' => $license->id, 'gateway_mode' => 'production']);
        $this->get('/checkout/'.$paid->number)->assertOk()->assertSee($key)->assertSee('Salin key')->assertDontSee('Hapus invoice');
    }
}
