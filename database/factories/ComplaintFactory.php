<?php

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\MasterRt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Complaint>
 */
class ComplaintFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'rt_id' => MasterRt::factory(),
            'title' => fake()->sentence(6),
            'description' => fake()->paragraph(),
            'photo' => null,
            'status' => 'menunggu_verifikasi_rt',
        ];
    }
}
