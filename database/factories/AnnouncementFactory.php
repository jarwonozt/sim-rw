<?php

namespace Database\Factories;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(8),
            'content' => fake()->paragraphs(3, true),
            'image' => null,
            'publish_date' => now()->toDateString(),
            'expire_date' => now()->addMonth()->toDateString(),
            'created_by' => User::factory()->role('ketua_rw'),
        ];
    }
}
