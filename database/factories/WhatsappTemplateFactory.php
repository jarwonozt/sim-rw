<?php

namespace Database\Factories;

use App\Models\WhatsappTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WhatsappTemplate>
 */
class WhatsappTemplateFactory extends Factory
{
    protected $model = WhatsappTemplate::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'event_key' => null,
            'content' => 'Halo [nama_warga], '.fake()->sentence(),
            'is_active' => true,
        ];
    }
}
