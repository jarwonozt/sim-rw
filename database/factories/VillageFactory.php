<?php

namespace Database\Factories;

use App\Models\Subdistrict;
use App\Models\Village;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Village>
 */
class VillageFactory extends Factory
{
    protected $model = Village::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->unique()->numberBetween(1101011001, 9999999999),
            'subdistrict_id' => Subdistrict::factory(),
            'name' => fake()->streetName(),
        ];
    }
}
