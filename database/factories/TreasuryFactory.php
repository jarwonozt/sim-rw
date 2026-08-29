<?php

namespace Database\Factories;

use App\Models\Treasury;
use App\Models\TreasuryCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Treasury>
 */
class TreasuryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'treasury_category_id' => TreasuryCategory::factory(),
            'type' => fake()->randomElement(['in', 'out']),
            'amount' => fake()->numberBetween(10000, 5000000),
            'description' => fake()->sentence(),
            'proof_photo' => 'proofs/placeholder.jpg',
            'transaction_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'created_by' => User::factory()->role('bendahara'),
        ];
    }
}
