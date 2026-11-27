<?php
namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\LicenseProduct;

class SalesOverviewService
{
    public function report(): array
    {
        $from=now('Asia/Jakarta')->startOfMonth()->utc();$to=now('Asia/Jakarta')->endOfDay()->utc();
        $live=PurchaseOrder::query()->where(fn ($query)=>$query->whereNull('gateway_mode')->orWhere('gateway_mode','production'));
        $paid=(clone $live)->where('status','paid')->whereBetween('paid_at',[$from,$to]);
        $cohort=(clone $live)->whereBetween('created_at',[$from,$to]);
        $total=(clone $cohort)->count();$cohortPaid=(clone $cohort)->where('status','paid')->count();
        $pending=(clone $live)->where('status','pending');
        $review=(clone $pending)->whereIn('gateway_status',['creating','uncertain','fulfillment_pending','failed','expired'])->count();
        $count=(clone $paid)->count();$revenue=(int)(clone $paid)->sum('amount');
        return [
            'period'=>now('Asia/Jakarta')->translatedFormat('F Y'),
            'paid_count'=>$count,'revenue'=>$revenue,'average'=>$count>0?(int)round($revenue/$count):0,
            'renewal_count'=>(clone $paid)->where('purpose','renewal')->count(),
            'renewal_revenue'=>(int)(clone $paid)->where('purpose','renewal')->sum('amount'),
            'pending_count'=>(clone $pending)->count(),'pending_amount'=>(int)(clone $pending)->sum('amount'),
            'cancelled_count'=>(clone $cohort)->where('status','cancelled')->count(),
            'conversion'=>$total>0?round($cohortPaid/$total*100,1):0,'cohort_total'=>$total,
            'review_count'=>$review,'active_packages'=>LicenseProduct::where('is_active',true)->count(),
            'bestsellers'=>(clone $paid)->selectRaw('product_name, COUNT(*) as sales_count, SUM(amount) as revenue')->groupBy('product_name')->orderByDesc('sales_count')->orderByDesc('revenue')->limit(5)->get(),
            'methods'=>(clone $paid)->selectRaw('payment_method, COUNT(*) as sales_count, SUM(amount) as revenue')->groupBy('payment_method')->orderByDesc('revenue')->limit(5)->get(),
            'recent'=>(clone $live)->latest('id')->limit(5)->get(),
        ];
    }
}
