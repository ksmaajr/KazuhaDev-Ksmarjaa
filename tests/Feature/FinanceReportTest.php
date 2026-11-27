<?php

namespace Tests\Feature;

use App\Models\FinancialTransaction;
use App\Models\User;
use App\Services\FinanceReportService;
use App\Services\XlsxReportExporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

class FinanceReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_chart_and_xlsx_reconcile_gross_fees_expenses_and_net_from_same_rows(): void
    {
        FinancialTransaction::factory()->create(['amount' => 100000, 'fee' => 2500, 'paid_at' => '2026-11-26 17:00:00']);
        FinancialTransaction::factory()->create(['amount' => 50000, 'fee' => 1000, 'source' => 'gateway', 'paid_at' => '2026-11-27 03:00:00']);
        FinancialTransaction::factory()->create(['direction' => 'expense', 'amount' => 10000, 'paid_at' => '2026-11-27 04:00:00']);
        FinancialTransaction::factory()->create(['amount' => 999999, 'status' => 'void', 'paid_at' => '2026-11-27 04:00:00']);
        FinancialTransaction::factory()->create(['amount' => 888888, 'paid_at' => '2026-11-26 16:59:59']);
        $r = app(FinanceReportService::class)->report('2026-11-27', '2026-11-27');
        $this->assertSame(150000, $r['income']);
        $this->assertSame(3500, $r['fees']);
        $this->assertSame(10000, $r['expenses']);
        $this->assertSame(136500, $r['net']);
        $this->assertSame(50000, $r['gateway']);
        $this->assertSame($r['income'], $r['daily']->sum('income'));
        $this->assertSame($r['expenses'], $r['daily']->sum('expense'));
        $this->assertSame($r['fees'], $r['daily']->sum('fee'));
        $this->actingAs(User::factory()->create())->get('/finance?from=2026-11-27&to=2026-11-27')->assertOk()->assertSee('150.000')->assertSee('136.500');
        $response = $this->get('/finance/export?from=2026-11-27&to=2026-11-27')->assertOk()->assertDownload('KazuhaDev-keuangan-2026-11-27-2026-11-27.xlsx');
        $path = $response->baseResponse->getFile()->getPathname();
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path));
        try {
            $this->assertStringContainsString('<c r="B3" s="2"><v>150000</v></c>', $zip->getFromName('xl/worksheets/sheet1.xml'));
            $this->assertStringContainsString('<c r="B6" s="2"><v>136500</v></c>', $zip->getFromName('xl/worksheets/sheet1.xml'));
            $this->assertStringContainsString('2026-11-27 00:00:00', $zip->getFromName('xl/worksheets/sheet2.xml'));
            $this->assertStringNotContainsString('888888', $zip->getFromName('xl/worksheets/sheet2.xml'));
            foreach (['xl/workbook.xml', 'xl/worksheets/sheet1.xml', 'xl/worksheets/sheet2.xml', 'xl/styles.xml', '[Content_Types].xml'] as $file) {
                $this->assertNotFalse(simplexml_load_string($zip->getFromName($file)));
            }
        } finally {
            $zip->close();
            unlink($path);
        }
    }

    public function test_manual_entries_are_integer_idr_and_cannot_impersonate_gateway(): void
    {
        $this->actingAs(User::factory()->create());
        $base = ['direction' => 'income', 'amount' => 15000, 'fee' => 500, 'description' => 'Test payment', 'paid_at' => '2026-11-27T10:30', 'external_reference' => 'BANK-001'];
        $this->post('/finance', $base)->assertSessionHasNoErrors()->assertRedirect('/finance');
        $row = FinancialTransaction::firstOrFail();
        $this->assertSame('manual', $row->source);
        $this->assertSame('2026-11-27 03:30:00', $row->paid_at->format('Y-m-d H:i:s'));
        $this->post('/finance', $base)->assertSessionHasErrors('external_reference');
        $this->post('/finance', array_replace($base, ['external_reference' => 'x', 'source' => 'gateway']))->assertSessionHasErrors('source');
        $this->post('/finance', array_replace($base, ['external_reference' => 'x', 'amount' => '12.5']))->assertSessionHasErrors('amount');
        $this->post('/finance', array_replace($base, ['external_reference' => 'x', 'fee' => 16000]))->assertSessionHasErrors('fee');
        $this->post('/finance', array_replace($base, ['external_reference' => 'x', 'direction' => 'expense']))->assertSessionHasErrors('fee');
        $this->assertDatabaseCount('financial_transactions', 1);
    }

    public function test_void_is_audited_and_not_counted_without_deleting_ledger(): void
    {
        $row = FinancialTransaction::factory()->create(['amount' => 10000]);
        $this->actingAs(User::factory()->create())->post('/finance/'.$row->id.'/void')->assertSessionHasNoErrors();
        $this->assertSame('void', $row->fresh()->status);
        $this->assertDatabaseCount('financial_transactions', 1);
        $this->assertDatabaseHas('license_events', ['action' => 'finance_voided']);
        $r = app(FinanceReportService::class)->report(now('Asia/Jakarta')->toDateString(), now('Asia/Jakarta')->toDateString());
        $this->assertSame(0, $r['net']);
    }

    public function test_guests_and_member_roles_cannot_read_or_export_finance(): void
    {
        $this->get('/finance')->assertRedirect('/login');
        $this->get('/finance/export')->assertRedirect('/login');
        $u = User::factory()->create();
        $u->role = 'member';
        $u->save();
        $this->actingAs($u)->get('/finance')->assertForbidden();
        $this->get('/finance/export')->assertForbidden();
    }

    public function test_export_keeps_user_text_as_text_not_spreadsheet_formulas(): void
    {
        FinancialTransaction::factory()->create(['description' => '=HYPERLINK("bad")']);
        $r = app(FinanceReportService::class)->report(now('Asia/Jakarta')->toDateString(), now('Asia/Jakarta')->toDateString());
        $path = app(XlsxReportExporter::class)->export($r);
        $zip = new ZipArchive;
        $zip->open($path);
        try {
            $xml = $zip->getFromName('xl/worksheets/sheet2.xml');
            $this->assertStringNotContainsString('<f>', $xml);
            $this->assertStringContainsString('=HYPERLINK(&quot;bad&quot;)',$xml);
        } finally {
            $zip->close();
            unlink($path);
        }
    }
}
