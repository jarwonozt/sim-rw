<?php

namespace Tests\Feature\Inventory;

use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_bendahara_cannot_access_inventory_categories(): void
    {
        $bendahara = User::factory()->role('bendahara')->create();

        $this->actingAs($bendahara)->get(route('inventory-categories.index'))->assertForbidden();
    }

    public function test_ketua_rt_cannot_manage_inventory_categories(): void
    {
        $ketuaRt = User::factory()->role('ketua_rt')->create();

        $this->actingAs($ketuaRt)->get(route('inventory-categories.index'))->assertForbidden();
    }

    public function test_sekretaris_can_create_a_category(): void
    {
        $sekretaris = User::factory()->role('sekretaris')->create();

        $response = $this->actingAs($sekretaris)->post(route('inventory-categories.store'), [
            'name' => 'Peralatan Acara',
        ]);

        $response->assertRedirect(route('inventory-categories.index'));
        $this->assertDatabaseHas('inventory_categories', ['name' => 'Peralatan Acara']);
    }

    public function test_category_cannot_be_deleted_while_still_used_by_an_item(): void
    {
        $ketuaRw = User::factory()->role('ketua_rw')->create();
        $category = InventoryCategory::factory()->create();
        InventoryItem::factory()->create(['inventory_category_id' => $category->id]);

        $response = $this->actingAs($ketuaRw)->delete(route('inventory-categories.destroy', $category));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('inventory_categories', ['id' => $category->id]);
    }
}
