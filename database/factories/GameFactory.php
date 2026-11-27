<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class GameFactory extends Factory
{
    public function definition(): array
    {
        return ['name' => fake()->unique()->words(3, true), 'code' => fake()->unique()->slug(3)];
    }
}
