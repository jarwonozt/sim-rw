<?php

namespace Database\Factories;

use App\Models\MasterRt;
use App\Models\MasterRw;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MasterRt>
 */
class MasterRtFactory extends Factory
{
    protected $model = MasterRt::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'master_rw_id' => MasterRw::factory(),
            'nomor_rt' => str_pad((string) fake()->unique()->numberBetween(1, 20), 3, '0', STR_PAD_LEFT),
            'ketua_rt_id' => null,
        ];
    }
}
