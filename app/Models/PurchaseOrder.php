<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as QueryBuilder;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['amount' => 'integer', 'days' => 'integer', 'duration_seconds' => 'integer', 'game_ids' => 'array', 'game_snapshot' => 'array', 'gateway_expires_at' => 'immutable_datetime', 'gateway_fee' => 'integer', 'paid_at' => 'immutable_datetime', 'verified_at' => 'immutable_datetime', 'cancelled_at' => 'immutable_datetime'];
    }

    public function canBeDeleted(): bool
    {
        return $this->status === 'pending'
            && $this->gateway_status === null
            && $this->gateway_payment_id === null
            && $this->gateway_payment_url === null
            && $this->issued_license_id === null
            && $this->paid_at === null
            && $this->payment_reference === null;
    }

    public function scopeDeletable(Builder $query): Builder
    {
        return $query->where('status', 'pending')
            ->whereNull('gateway_status')->whereNull('gateway_payment_id')->whereNull('gateway_payment_url')
            ->whereNull('issued_license_id')->whereNull('paid_at')->whereNull('payment_reference')
            ->whereNotExists(fn (QueryBuilder $transactions) => $transactions->selectRaw('1')->from('financial_transactions')->whereColumn('financial_transactions.order_id', 'purchase_orders.id'));
    }

    public function issuedLicense(): BelongsTo
    {
        return $this->belongsTo(LicenseKey::class, 'issued_license_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(LicenseKey::class, 'owner_license_id')->withTrashed();
    }
}
