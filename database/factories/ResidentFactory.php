<?php

namespace Database\Factories;

use App\Models\FamilyHead;
use App\Models\Resident;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Resident>
 */
class ResidentFactory extends Factory
{
    protected $model = Resident::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = fake()->randomElement(['L', 'P']);

        return [
            'family_head_id' => FamilyHead::factory(),
            'nik' => fake()->unique()->numerify('################'),
            'name' => $gender === 'L' ? fake()->firstNameMale().' '.fake()->lastName() : fake()->firstNameFemale().' '.fake()->lastName(),
            'gender' => $gender,
            'birth_place' => fake()->city(),
            'birth_date' => fake()->dateTimeBetween('-80 years', '-1 years'),
            'is_family_head' => false,
            'relationship_status' => fake()->randomElement(['Kepala Keluarga', 'Istri', 'Anak']),
            'occupation' => fake()->jobTitle(),
            'religion' => fake()->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu']),
            'education' => fake()->randomElement(['SD', 'SMP', 'SMA', 'D3', 'S1', 'S2']),
            'marital_status' => fake()->randomElement(['Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati']),
            'phone' => fake()->phoneNumber(),
            'photo' => null,
        ];
    }

    public function familyHeadRole(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_family_head' => true,
            'relationship_status' => 'Kepala Keluarga',
        ]);
    }
}
