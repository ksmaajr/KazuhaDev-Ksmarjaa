<?php

namespace App\Services;

use App\Models\FinancialTransaction;
use App\Models\PurchaseOrder;
use App\Support\ListFilters;
use Carbon\CarbonImmutable;

class FinanceReportService
{
    public function report(string $from, string $to, array $filters = []): array
    {
        $start = CarbonImmutable::parse($from, 'Asia/Jakarta')->startOfDay();
        $end = CarbonImmutable::parse($to, 'Asia/Jakarta')->endOfDay();
        $query = FinancialTransaction::whereBetween('paid_at', [$start->utc(), $end->utc()]);
        ListFilters::search($query, $filters, ['reference', 'description', 'external_reference']);
        foreach (['status', 'source', 'direction'] as $field) {
            if ($filters[$field] ?? null) {
                $query->where($field, $filters[$field]);
            }
        }
        if ($filters['method'] ?? null) {
            $query->whereIn('order_id', PurchaseOrder::where('payment_method', $filters['method'])->select('id'));
        }
        if (isset($filters['min_amount'])) {
            $query->where('amount', '>=', $filters['min_amount']);
        }
        if (isset($filters['max_amount'])) {
            $query->where('amount', '<=', $filters['max_amount']);
        }
        ListFilters::sort($query, $filters, 'description', 'amount');
        $rows = $query->get();
        $posted = $rows->where('status', 'posted');
        $income = (int) $posted->where('direction', 'income')->sum('amount');
        $fees = (int) $posted->where('direction', 'income')->sum('fee');
        $expenses = (int) $posted->where('direction', 'expense')->sum('amount');
        $gateway = (int) $posted->where('direction', 'income')->where('source', 'gateway')->sum('amount');
        $daily = collect();
        for ($day = $start; $day->lte($end); $day = $day->addDay()) {
            $items = $posted->filter(fn ($r) => $r->paid_at->timezone('Asia/Jakarta')->toDateString() === $day->toDateString());
            $daily->push(['date' => $day->toDateString(), 'label' => $day->format('d M'), 'income' => (int) $items->where('direction', 'income')->sum('amount'), 'expense' => (int) $items->where('direction', 'expense')->sum('amount'), 'fee' => (int) $items->where('direction', 'income')->sum('fee')]);
        }

        return compact('rows', 'income', 'fees', 'expenses', 'gateway', 'daily', 'from', 'to') + ['net' => $income - $fees - $expenses, 'currency' => 'IDR'];
    }
}
