<?php

namespace App\Console\Commands;

use App\Models\LicenseEvent;
use App\Models\PurchaseOrder;
use App\Services\PurchaseService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class PruneUnpaidInvoices extends Command
{
    protected $signature = 'invoices:prune {--dry-run : Count eligible invoices without deleting them}';

    protected $description = 'Delete unpaid invoices older than 24 hours that have never been sent to a payment gateway';

    public function handle(PurchaseService $service): int
    {
        $cutoff = CarbonImmutable::now()->subDay();
        $query = PurchaseOrder::deletable()->where('created_at', '<=', $cutoff);
        if ($this->option('dry-run')) {
            $this->info($query->count().' invoice memenuhi syarat pembersihan.');

            return self::SUCCESS;
        }
        $deleted = 0;
        $query->chunkById(100, function (Collection $orders) use ($service, $cutoff, &$deleted): void {
            foreach ($orders as $order) {
                if ($service->deleteUnpaid($order, null, null, $cutoff)) {
                    $deleted++;
                }
            }
        });
        if ($deleted > 0) {
            LicenseEvent::create(['action' => 'invoices_pruned', 'metadata' => ['deleted_count' => $deleted, 'retention_hours' => 24]]);
        }
        $this->info($deleted.' invoice belum dibayar dihapus.');

        return self::SUCCESS;
    }
}
