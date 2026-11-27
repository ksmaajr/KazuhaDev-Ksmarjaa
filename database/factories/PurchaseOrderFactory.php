<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\LicenseKey;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderFactory extends Factory
{
    public function definition(): array
    {
        return ['number' => 'KZ-TEST-'.str_replace('-', '', fake()->unique()->uuid()), 'idempotency_key' => fake()->unique()->uuid(), 'owner_license_id' => LicenseKey::factory(), 'member_name' => 'Test Member', 'product_name' => 'Premium ROS', 'days' => 7, 'game_ids' => [Game::where('code', 'rules-of-survival')->firstOrFail()->id], 'amount' => 50000, 'currency' => 'IDR', 'status' => 'pending', 'payment_method' => 'manual'];
    }
}
