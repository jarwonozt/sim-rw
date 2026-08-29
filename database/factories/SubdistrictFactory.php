<?php

namespace Database\Factories;

use App\Models\District;
use App\Models\Subdistrict;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subdistrict>
 */
class SubdistrictFactory extends Factory
{
    protected $model = Subdistrict::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->unique()->numberBetween(110101, 999999),
            'district_id' => District::factory(),
            'name' => fake()->citySuffix(),
        ];
    }
}
