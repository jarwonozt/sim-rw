<?php

namespace Database\Factories;

use App\Models\FamilyHead;
use App\Models\MasterRt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FamilyHead>
 */
class FamilyHeadFactory extends Factory
{
    protected $model = FamilyHead::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rt_id' => MasterRt::factory(),
            'no_kk' => fake()->unique()->numerify('################'),
            'address' => fake()->streetAddress(),
            'postal_code' => fake()->postcode(),
        ];
    }
}
