<?php

namespace Tests\Feature\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryLoan;
use App\Models\MasterRt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryLoanTest extends TestCase
{
    use RefreshDatabase;

    public function test_bendahara_cannot_access_inventory_loans(): void
    {
        $bendahara = User::factory()->role('bendahara')->create();

        $this->actingAs($bendahara)->get(route('inventory-loans.index'))->assertForbidden();
    }

    public function test_sekretaris_can_record_a_loan(): void
    {
        $sekretaris = User::factory()->role('sekretaris')->create();
        $item = InventoryItem::factory()->create(['quantity' => 10]);

        $response = $this->actingAs($sekretaris)->post(route('inventory-loans.store'), [
            'inventory_item_id' => $item->id,
            'borrower_name' => 'Budi',
            'quantity_borrowed' => 3,
            'purpose' => 'Hajatan warga',
            'loan_date' => now()->toDateString(),
            'due_date' => now()->addDays(3)->toDateString(),
        ]);

        $response->assertRedirect(route('inventory-loans.index'));
        $this->assertDatabaseHas('inventory_loans', [
            'inventory_item_id' => $item->id,
            'borrower_name' => 'Budi',
            'quantity_borrowed' => 3,
            'status' => 'dipinjam',
        ]);
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

        $response = $this->actingAs($sekretaris)->post(route('inventory-loans.store'), [
            'inventory_item_id' => $item->id,
            'borrower_name' => 'Citra',
            'quantity_borrowed' => 2,
            'purpose' => 'Rapat RT',
            'loan_date' => now()->toDateString(),
            'due_date' => now()->addDays(3)->toDateString(),
        ]);

        $response->assertSessionHasErrors('quantity_borrowed');
        $this->assertDatabaseMissing('inventory_loans', ['borrower_name' => 'Citra']);
    }

    public function test_marking_a_loan_returned_in_good_condition_keeps_item_quantity(): void
    {
        $ketuaRw = User::factory()->role('ketua_rw')->create();
        $item = InventoryItem::factory()->create(['quantity' => 10]);
        $loan = InventoryLoan::factory()->create([
            'inventory_item_id' => $item->id,
            'quantity_borrowed' => 4,
            'status' => 'dipinjam',
        ]);

        $response = $this->actingAs($ketuaRw)->patch(route('inventory-loans.return', $loan), [
            'returned_condition' => 'baik',
        ]);

        $response->assertRedirect(route('inventory-loans.index'));
        $this->assertDatabaseHas('inventory_loans', ['id' => $loan->id, 'status' => 'dikembalikan']);
        $this->assertDatabaseHas('inventory_items', ['id' => $item->id, 'quantity' => 10]);
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

        $response = $this->actingAs($ketuaRw)->patch(route('inventory-loans.return', $loan), [
            'returned_condition' => 'hilang',
        ]);

        $response->assertRedirect(route('inventory-loans.index'));
        $this->assertDatabaseHas('inventory_loans', ['id' => $loan->id, 'status' => 'hilang']);
        $this->assertDatabaseHas('inventory_items', ['id' => $item->id, 'quantity' => 6]);
    }

    public function test_a_loan_already_returned_cannot_be_returned_again(): void
    {
        $ketuaRw = User::factory()->role('ketua_rw')->create();
        $loan = InventoryLoan::factory()->create(['status' => 'dikembalikan']);

        $response = $this->actingAs($ketuaRw)->patch(route('inventory-loans.return', $loan), [
            'returned_condition' => 'baik',
        ]);

        $response->assertSessionHas('error');
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

        $response = $this->actingAs($ketuaRt)->get(route('inventory-loans.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->has('loans.data', 1));
    }
}
