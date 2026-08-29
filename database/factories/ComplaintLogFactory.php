<?php

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\ComplaintLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComplaintLog>
 */
class ComplaintLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'complaint_id' => Complaint::factory(),
            'status' => 'menunggu_verifikasi_rt',
            'note' => fake()->optional()->sentence(),
            'changed_by' => User::factory(),
        ];
    }
}
