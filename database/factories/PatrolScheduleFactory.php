<?php

namespace Database\Factories;

use App\Models\MasterRt;
use App\Models\PatrolSchedule;
use App\Models\Resident;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PatrolSchedule>
 */
class PatrolScheduleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rt_id' => MasterRt::factory(),
            'resident_id' => Resident::factory(),
            'schedule_date' => fake()->dateTimeBetween('now', '+1 month'),
            'shift' => fake()->randomElement(['19:00 - 22:00', '22:00 - 01:00', '01:00 - 04:00']),
            'status' => 'scheduled',
        ];
    }
}
