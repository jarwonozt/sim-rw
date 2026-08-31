<?php

namespace Tests\Feature\Api\V1;

use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryLoan;
use App\Models\MasterRt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_bendahara_cannot_access_inventory_items(): void
    {
        $bendahara = User::factory()->role('bendahara')->create();

        $this->actingAs($bendahara, 'sanctum')->getJson(route('api.v1.inventory-items.index'))->assertForbidden();
    }

    public function test_sekretaris_can_create_an_item_with_an_auto_generated_code(): void
    {
        $sekretaris = User::factory()->role('sekretaris')->create();
        $category = InventoryCategory::factory()->create();

        $response = $this->actingAs($sekretaris, 'sanctum')->postJson(route('api.v1.inventory-items.store'), [
            'inventory_category_id' => $category->id,
            'name' => 'Kursi Lipat',
            'quantity' => 20,
            'condition' => 'baik',
        ]);

        $response->assertCreated();
        $item = InventoryItem::query()->where('name', 'Kursi Lipat')->firstOrFail();
        $this->assertSame('INV-'.now()->year.'-0001', $item->code);
    }

    public function test_ketua_rt_only_sees_items_belonging_to_their_own_rt(): void
    {
        $ketuaRt = User::factory()->role('ketua_rt')->create();
        $rt1 = MasterRt::factory()->create(['ketua_rt_id' => $ketuaRt->id]);
        $rt2 = MasterRt::factory()->create();

        InventoryItem::factory(2)->create(['rt_id' => $rt1->id]);
        InventoryItem::factory(3)->create(['rt_id' => $rt2->id]);

        $response = $this->actingAs($ketuaRt, 'sanctum')->getJson(route('api.v1.inventory-items.index'));

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
    }

    public function test_loan_is_rejected_when_quantity_exceeds_available_stock(): void
    {
        $sekretaris = User::factory()->role('sekretaris')->create();
        $item = InventoryItem::factory()->create(['quantity' => 5]);
        InventoryLoan::factory()->create([
            'inventory_item_id' => $item->id,
            'quantity_borrowed' => 4,
            'status' => 'dipinjam',
        ]);

        $response = $this->actingAs($sekretaris, 'sanctum')->postJson(route('api.v1.inventory-loans.store'), [
            'inventory_item_id' => $item->id,
            'borrower_name' => 'Citra',
            'quantity_borrowed' => 2,
            'purpose' => 'Rapat RT',
            'loan_date' => now()->toDateString(),
            'due_date' => now()->addDays(3)->toDateString(),
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('inventory_loans', ['borrower_name' => 'Citra']);
    }

    public function test_marking_a_loan_lost_decrements_item_quantity(): void
    {
        $ketuaRw = User::factory()->role('ketua_rw')->create();
        $item = InventoryItem::factory()->create(['quantity' => 10]);
        $loan = InventoryLoan::factory()->create([
            'inventory_item_id' => $item->id,
            'quantity_borrowed' => 4,
            'status' => 'dipinjam',
        ]);

        $response = $this->actingAs($ketuaRw, 'sanctum')->patchJson(route('api.v1.inventory-loans.return', $loan), [
            'returned_condition' => 'hilang',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('inventory_loans', ['id' => $loan->id, 'status' => 'hilang']);
        $this->assertDatabaseHas('inventory_items', ['id' => $item->id, 'quantity' => 6]);
    }

    public function test_ketua_rt_only_sees_loans_for_items_in_their_own_rt(): void
    {
        $ketuaRt = User::factory()->role('ketua_rt')->create();
        $rt1 = MasterRt::factory()->create(['ketua_rt_id' => $ketuaRt->id]);
        $rt2 = MasterRt::factory()->create();

        $ownItem = InventoryItem::factory()->create(['rt_id' => $rt1->id]);
        $otherItem = InventoryItem::factory()->create(['rt_id' => $rt2->id]);
        InventoryLoan::factory()->create(['inventory_item_id' => $ownItem->id]);
        InventoryLoan::factory()->create(['inventory_item_id' => $otherItem->id]);

        $response = $this->actingAs($ketuaRt, 'sanctum')->getJson(route('api.v1.inventory-loans.index'));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }
}
