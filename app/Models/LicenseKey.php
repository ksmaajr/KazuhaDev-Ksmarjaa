<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LicenseKey extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $hidden = ['key_hash', 'hwid_hash', 'hwid', 'key_value'];

    protected function casts(): array
    {
        return ['key_value' => 'encrypted', 'hwid' => 'encrypted', 'expires_at' => 'immutable_datetime', 'activated_at' => 'immutable_datetime', 'bound_at' => 'immutable_datetime', 'last_seen_at' => 'immutable_datetime', 'revoked_at' => 'immutable_datetime', 'duration_seconds' => 'integer'];
    }

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class)->withTimestamps();
    }

    public function events(): HasMany
    {
        return $this->hasMany(LicenseEvent::class);
    }

    public function status(): string
    {
        return match (true) {
            $this->revoked_at !== null => 'revoked', $this->expires_at?->lessThanOrEqualTo(now()) => 'expired', $this->hwid_hash !== null => 'active', default => 'unbound'
        };
    }
}
