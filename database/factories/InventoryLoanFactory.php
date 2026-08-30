<?php

namespace Database\Factories;

use App\Models\InventoryItem;
use App\Models\InventoryLoan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryLoan>
 */
class InventoryLoanFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'inventory_item_id' => InventoryItem::factory(),
            'resident_id' => null,
            'borrower_name' => fake()->name(),
            'borrower_phone' => fake()->phoneNumber(),
            'quantity_borrowed' => fake()->numberBetween(1, 3),
            'purpose' => 'Acara warga',
            'loan_date' => now()->toDateString(),
            'due_date' => now()->addDays(3)->toDateString(),
            'return_date' => null,
            'returned_condition' => null,
            'status' => 'dipinjam',
            'handled_by' => User::factory()->role('sekretaris'),
            'notes' => null,
        ];
    }
}
