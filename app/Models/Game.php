<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Game extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $hidden = ['injector_path', 'dll_path', 'banner_path', 'logo_path', 'dll_variants'];

    protected function casts(): array
    {
        return ['injector_size' => 'integer', 'dll_size' => 'integer', 'dll_variants' => 'array'];
    }

    public function artworkUrl(string $kind): ?string
    {
        return $this->{$kind.'_path'} || $this->code === 'rules-of-survival' ? route('games.artwork', [$this, $kind, 'v' => $this->updated_at?->getTimestamp()]) : null;
    }

    public function licenses(): BelongsToMany
    {
        return $this->belongsToMany(LicenseKey::class)->withTimestamps();
    }
}
