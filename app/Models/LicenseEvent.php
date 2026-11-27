<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenseEvent extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function licenseKey(): BelongsTo
    {
        return $this->belongsTo(LicenseKey::class)->withTrashed();
    }
}
