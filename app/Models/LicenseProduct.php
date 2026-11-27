<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicenseProduct extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['price' => 'integer', 'days' => 'integer', 'duration_seconds' => 'integer', 'game_ids' => 'array', 'is_active' => 'boolean'];
    }
}
