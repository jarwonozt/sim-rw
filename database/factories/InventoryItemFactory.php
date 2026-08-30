<?php

namespace Database\Factories;

use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryItem>
 */
class InventoryItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'inventory_category_id' => InventoryCategory::factory(),
            'rt_id' => null,
            'code' => 'INV-'.now()->year.'-'.str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'name' => fake()->randomElement(['Kursi Lipat', 'Tenda', 'Sound System', 'Meja Panjang', 'Terpal']),
            'quantity' => fake()->numberBetween(5, 50),
            'condition' => 'baik',
            'location' => 'Gudang Balai RW',
            'photo' => null,
            'notes' => null,
            'created_by' => User::factory()->role('sekretaris'),
        ];
    }
}
