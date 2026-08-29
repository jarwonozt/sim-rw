<?php

namespace Database\Factories;

use App\Models\LetterTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LetterTemplate>
 */
class LetterTemplateFactory extends Factory
{
    protected $model = LetterTemplate::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Surat Keterangan '.fake()->word(),
            'type' => fake()->randomElement(['domisili', 'sktm', 'usaha']),
            'content' => '<p>Yang bertanda tangan di bawah ini menerangkan bahwa [nama_penduduk] dengan NIK [nik] benar merupakan warga kami.</p>',
            'is_active' => true,
        ];
    }
}
