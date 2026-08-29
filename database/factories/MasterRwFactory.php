<?php

namespace Database\Factories;

use App\Models\MasterRw;
use App\Models\Village;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MasterRw>
 */
class MasterRwFactory extends Factory
{
    protected $model = MasterRw::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'village_id' => Village::factory(),
            'nomor_rw' => str_pad((string) fake()->unique()->numberBetween(1, 20), 3, '0', STR_PAD_LEFT),
            'ketua_rw_id' => null,
            'address' => fake()->address(),
        ];
    }
}
