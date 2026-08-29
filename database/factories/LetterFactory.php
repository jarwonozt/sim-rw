<?php

namespace Database\Factories;

use App\Models\Letter;
use App\Models\LetterTemplate;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Letter>
 */
class LetterFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'letter_number' => fake()->unique()->numerify('###/RW-01/####'),
            'letter_template_id' => LetterTemplate::factory(),
            'resident_id' => Resident::factory(),
            'issued_by' => User::factory()->role('sekretaris'),
            'purpose' => fake()->sentence(),
            'issued_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'file_path' => null,
        ];
    }
}
