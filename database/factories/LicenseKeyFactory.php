<?php

namespace Database\Factories;

use App\Models\LicenseKey;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LicenseKey> */
class LicenseKeyFactory extends Factory
{
    public function definition(): array
    {
        return ['label' => fake()->name(), 'key_hash' => hash('sha256', fake()->uuid()), 'key_hint' => 'KF-TEST••••0001', 'duration_seconds' => 604800, 'expires_at' => null];
    }
}
