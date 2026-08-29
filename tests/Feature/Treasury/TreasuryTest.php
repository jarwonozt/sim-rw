<?php

namespace Tests\Feature\Treasury;

use App\Models\Treasury;
use App\Models\TreasuryCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TreasuryTest extends TestCase
{
    use RefreshDatabase;

    public function test_sekretaris_cannot_access_treasury(): void
    {
        $sekretaris = User::factory()->role('sekretaris')->create();

        $this->actingAs($sekretaris)->get(route('treasuries.index'))->assertForbidden();
    }

    public function test_ketua_rt_cannot_access_treasury(): void
    {
        $ketuaRt = User::factory()->role('ketua_rt')->create();

        $this->actingAs($ketuaRt)->get(route('treasuries.index'))->assertForbidden();
    }

    public function test_bendahara_can_record_a_transaction_with_a_proof_photo(): void
    {
        Storage::fake('public');

        $bendahara = User::factory()->role('bendahara')->create();
        $category = TreasuryCategory::factory()->create(['type' => 'in']);

        $response = $this->actingAs($bendahara)->post(route('treasuries.store'), [
            'treasury_category_id' => $category->id,
            'amount' => 150000,
            'transaction_date' => now()->toDateString(),
            'description' => 'Iuran bulan ini',
            'proof_photo' => UploadedFile::fake()->image('struk.jpg'),
        ]);

        $response->assertRedirect(route('treasuries.index'));
        $this->assertDatabaseHas('treasuries', [
            'treasury_category_id' => $category->id,
            'type' => 'in',
            'amount' => 150000,
            'created_by' => $bendahara->id,
        ]);

        $treasury = Treasury::first();
        Storage::disk('public')->assertExists($treasury->proof_photo);
    }

    public function test_transaction_type_is_derived_from_its_category(): void
    {
        Storage::fake('public');

        $bendahara = User::factory()->role('bendahara')->create();
        $expenseCategory = TreasuryCategory::factory()->create(['type' => 'out']);

        $this->actingAs($bendahara)->post(route('treasuries.store'), [
            'treasury_category_id' => $expenseCategory->id,
            'amount' => 50000,
            'transaction_date' => now()->toDateString(),
            'description' => 'Beli sapu',
            'proof_photo' => UploadedFile::fake()->image('nota.jpg'),
        ]);

        $this->assertDatabaseHas('treasuries', [
            'treasury_category_id' => $expenseCategory->id,
            'type' => 'out',
        ]);
    }

    public function test_proof_photo_is_required(): void
    {
        $bendahara = User::factory()->role('bendahara')->create();
        $category = TreasuryCategory::factory()->create();

        $response = $this->actingAs($bendahara)->post(route('treasuries.store'), [
            'treasury_category_id' => $category->id,
            'amount' => 50000,
            'transaction_date' => now()->toDateString(),
            'description' => 'Tanpa bukti',
        ]);

        $response->assertSessionHasErrors('proof_photo');
    }

    public function test_deleting_a_transaction_removes_its_proof_photo(): void
    {
        Storage::fake('public');

        $bendahara = User::factory()->role('bendahara')->create();
        $path = UploadedFile::fake()->image('struk.jpg')->store('treasuries', 'public');
        $treasury = Treasury::factory()->create(['proof_photo' => $path]);

        $this->actingAs($bendahara)->delete(route('treasuries.destroy', $treasury));

        $this->assertDatabaseMissing('treasuries', ['id' => $treasury->id]);
        Storage::disk('public')->assertMissing($path);
    }
}
