<?php

namespace Tests\Feature\Inventory;

use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryLoan;
use App\Models\MasterRt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_bendahara_cannot_access_inventory_items(): void
    {
        $bendahara = User::factory()->role('bendahara')->create();

        $this->actingAs($bendahara)->get(route('inventory-items.index'))->assertForbidden();
    }

    public function test_warga_cannot_access_inventory_items(): void
    {
        $warga = User::factory()->role('warga')->create();

        $this->actingAs($warga)->get(route('inventory-items.index'))->assertForbidden();
    }

    public function test_sekretaris_can_create_an_item_with_an_auto_generated_code(): void
    {
        $sekretaris = User::factory()->role('sekretaris')->create();
        $category = InventoryCategory::factory()->create();

        $response = $this->actingAs($sekretaris)->post(route('inventory-items.store'), [
            'inventory_category_id' => $category->id,
            'name' => 'Kursi Lipat',
            'quantity' => 20,
            'condition' => 'baik',
        ]);

        $response->assertRedirect(route('inventory-items.index'));
        $item = InventoryItem::query()->where('name', 'Kursi Lipat')->firstOrFail();
        $this->assertSame('INV-'.now()->year.'-0001', $item->code);
        $this->assertNull($item->rt_id);
    }

    public function test_ketua_rt_only_sees_items_belonging_to_their_own_rt(): void
    {
        $ketuaRt = User::factory()->role('ketua_rt')->create();
        $rt1 = MasterRt::factory()->create(['ketua_rt_id' => $ketuaRt->id]);
        $rt2 = MasterRt::factory()->create();

        InventoryItem::factory(2)->create(['rt_id' => $rt1->id]);
        InventoryItem::factory(3)->create(['rt_id' => $rt2->id]);
        InventoryItem::factory()->create(['rt_id' => null]);

        $response = $this->actingAs($ketuaRt)->get(route('inventory-items.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->has('items.data', 2));
    }

    public function test_ketua_rt_cannot_view_an_item_from_another_rt(): void
    {
        $ketuaRt = User::factory()->role('ketua_rt')->create();
        MasterRt::factory()->create(['ketua_rt_id' => $ketuaRt->id]);
        $otherRt = MasterRt::factory()->create();
        $item = InventoryItem::factory()->create(['rt_id' => $otherRt->id]);

        $this->actingAs($ketuaRt)->get(route('inventory-items.edit', $item))->assertNotFound();
    }

    public function test_ketua_rt_cannot_assign_an_item_to_another_rt(): void
    {
        $ketuaRt = User::factory()->role('ketua_rt')->create();
        MasterRt::factory()->create(['ketua_rt_id' => $ketuaRt->id]);
        $otherRt = MasterRt::factory()->create();
        $category = InventoryCategory::factory()->create();

        $response = $this->actingAs($ketuaRt)->post(route('inventory-items.store'), [
            'inventory_category_id' => $category->id,
            'rt_id' => $otherRt->id,
            'name' => 'Tenda',
            'quantity' => 5,
            'condition' => 'baik',
        ]);

        $response->assertRedirect(route('inventory-items.index'));
        $this->assertDatabaseMissing('inventory_items', ['name' => 'Tenda', 'rt_id' => $otherRt->id]);
    }

    public function test_quantity_cannot_be_reduced_below_units_currently_on_loan(): void
    {
        $ketuaRw = User::factory()->role('ketua_rw')->create();
        $category = InventoryCategory::factory()->create();
        $item = InventoryItem::factory()->create(['inventory_category_id' => $category->id, 'quantity' => 10]);
        InventoryLoan::factory()->create([
            'inventory_item_id' => $item->id,
            'quantity_borrowed' => 6,
            'status' => 'dipinjam',
        ]);

        $response = $this->actingAs($ketuaRw)->put(route('inventory-items.update', $item), [
            'inventory_category_id' => $category->id,
            'name' => $item->name,
            'quantity' => 5,
            'condition' => 'baik',
        ]);

        $response->assertSessionHasErrors('quantity');
        $this->assertDatabaseHas('inventory_items', ['id' => $item->id, 'quantity' => 10]);
    }

    public function test_item_with_loan_history_cannot_be_deleted(): void
    {
        $ketuaRw = User::factory()->role('ketua_rw')->create();
        $item = InventoryItem::factory()->create();
        InventoryLoan::factory()->create(['inventory_item_id' => $item->id, 'status' => 'dikembalikan']);

        $response = $this->actingAs($ketuaRw)->delete(route('inventory-items.destroy', $item));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('inventory_items', ['id' => $item->id]);
    }
}
