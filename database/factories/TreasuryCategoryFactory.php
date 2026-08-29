<?php

namespace Database\Factories;

use App\Models\TreasuryCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TreasuryCategory>
 */
class TreasuryCategoryFactory extends Factory
{
    protected $model = TreasuryCategory::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Iuran Warga', 'Sumbangan', 'Listrik', 'Konsumsi', 'Alat Kebersihan']),
            'type' => fake()->randomElement(['in', 'out']),
        ];
    }
}
