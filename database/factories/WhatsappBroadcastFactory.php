<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WhatsappBroadcast;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WhatsappBroadcast>
 */
class WhatsappBroadcastFactory extends Factory
{
    protected $model = WhatsappBroadcast::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $recipients = fake()->numberBetween(1, 20);
        $success = fake()->numberBetween(0, $recipients);

        return [
            'sent_by' => User::factory()->role('ketua_rw'),
            'rt_id' => null,
            'message' => fake()->paragraph(),
            'recipients_count' => $recipients,
            'success_count' => $success,
            'failed_count' => $recipients - $success,
        ];
    }
}
