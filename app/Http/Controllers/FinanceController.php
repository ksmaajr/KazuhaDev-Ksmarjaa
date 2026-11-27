<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFinancialTransactionRequest;
use App\Models\FinancialTransaction;
use App\Models\LicenseEvent;
use App\Services\FinanceReportService;
use App\Services\XlsxReportExporter;
use App\Support\ListFilters;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FinanceController extends Controller
{
    private function report(Request $request, FinanceReportService $service): array
    {
        $data = ListFilters::read($request, ['status' => ['nullable', 'in:posted,void'], 'direction' => ['nullable', 'in:income,expense'], 'source' => ['nullable', 'in:manual,gateway'], 'method' => ['nullable', 'in:manual,bank_transfer,e_wallet,qris,cash'], 'min_amount' => ['nullable', 'integer', 'between:0,100000000000'], 'max_amount' => ['nullable', 'integer', 'between:0,100000000000', ...($request->filled('min_amount') ? ['gte:min_amount'] : [])]]);
        $from = $data['from'] ?? now('Asia/Jakarta')->startOfMonth()->toDateString();
        $to = $data['to'] ?? now('Asia/Jakarta')->toDateString();
        if (CarbonImmutable::parse($from)->gt(CarbonImmutable::parse($to)) || CarbonImmutable::parse($from)->diffInDays(CarbonImmutable::parse($to)) > 366) {
            throw ValidationException::withMessages(['from' => 'Pilih rentang maksimal 366 hari dengan urutan tanggal yang benar.']);
        }

        return $service->report($from, $to, $data);
    }

    public function index(Request $request, FinanceReportService $service): View
    {
        $report = $this->report($request, $service);
        $limit = (int) ($request->input('per_page') ?: 10);
        $page = Paginator::resolveCurrentPage();
        $transactions = new LengthAwarePaginator($report['rows']->forPage($page, $limit)->values(), $report['rows']->count(), $limit, $page, ['path' => $request->url(), 'query' => $request->query()]);

        return view('finance', compact('report', 'transactions'));
    }

    public function export(Request $request, FinanceReportService $service, XlsxReportExporter $exporter): BinaryFileResponse
    {
        $report = $this->report($request, $service);
        $path = $exporter->export($report);

        return response()->download($path, 'KazuhaDev-keuangan-'.$report['from'].'-'.$report['to'].'.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'Cache-Control' => 'private, no-store'])->deleteFileAfterSend();
    }

    public function store(StoreFinancialTransactionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if ($data['direction'] === 'expense' && (int) $data['fee'] !== 0) {
            throw ValidationException::withMessages(['fee' => 'Biaya pembayaran terpisah hanya untuk pemasukan.']);
        }
        $data['paid_at'] = CarbonImmutable::createFromFormat('Y-m-d\TH:i', $data['paid_at'], 'Asia/Jakarta')->utc();
        DB::transaction(function () use ($data, $request): void {
            $row = FinancialTransaction::create($data + ['source' => 'manual', 'status' => 'posted', 'created_by' => $request->user()->id, 'reference' => 'MAN-'.Str::upper(Str::random(16))]);
            LicenseEvent::create(['user_id' => $request->user()->id, 'action' => 'finance_recorded', 'ip_address' => $request->ip(), 'metadata' => ['reference' => $row->reference, 'amount' => $row->amount, 'direction' => $row->direction]]);
        });

        return redirect()->route('finance.index')->with('success', 'Transaksi manual tercatat. Grafik dan laporan mengikuti ledger yang sama.');
    }

    public function void(Request $request, FinancialTransaction $transaction): RedirectResponse
    {
        DB::transaction(function () use ($transaction, $request): void {
            $row = FinancialTransaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail();
            if ($row->order_id) {
                throw ValidationException::withMessages(['transaction' => 'Transaksi pesanan tidak dapat di-void di sini. Gunakan proses refund terverifikasi ketika tersedia.']);
            }
            if ($row->status === 'void') {
                return;
            }
            $row->update(['status' => 'void']);
            LicenseEvent::create(['user_id' => $request->user()->id, 'action' => 'finance_voided', 'ip_address' => $request->ip(), 'metadata' => ['reference' => $row->reference, 'amount' => $row->amount]]);
        });

        return redirect()->route('finance.index')->with('success', 'Transaksi di-void; audit tersimpan dan tidak dihitung dalam total.');
    }
}
