<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class FinancialTransactionFactory extends Factory
{
    public function definition(): array
    {
        return ['reference' => 'TEST-'.fake()->unique()->uuid(), 'direction' => 'income', 'source' => 'manual', 'status' => 'posted', 'amount' => 100000, 'fee' => 0, 'currency' => 'IDR', 'description' => 'Test transaction', 'paid_at' => now()];
    }
}
