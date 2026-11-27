<?php

namespace Database\Factories;

use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;

class LicenseProductFactory extends Factory
{
    public function definition(): array
    {
        return ['name' => 'Premium ROS', 'description' => 'Test package', 'days' => 7, 'price' => 50000, 'game_ids' => [Game::where('code', 'rules-of-survival')->firstOrFail()->id], 'is_active' => true];
    }
}
