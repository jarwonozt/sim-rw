<?php

namespace Tests\Feature\Api\V1;

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

        $this->actingAs($sekretaris, 'sanctum')->getJson(route('api.v1.treasuries.index'))->assertForbidden();
    }

    public function test_bendahara_can_record_a_transaction_with_a_proof_photo(): void
    {
        Storage::fake('public');

        $bendahara = User::factory()->role('bendahara')->create();
        $category = TreasuryCategory::factory()->create(['type' => 'in']);

        $response = $this->actingAs($bendahara, 'sanctum')->post(route('api.v1.treasuries.store'), [
            'treasury_category_id' => $category->id,
            'amount' => 150000,
            'transaction_date' => now()->toDateString(),
            'description' => 'Iuran bulan ini',
            'proof_photo' => UploadedFile::fake()->image('struk.jpg'),
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('treasuries', [
            'treasury_category_id' => $category->id,
            'type' => 'in',
            'amount' => 150000,
            'created_by' => $bendahara->id,
        ]);
        Storage::disk('public')->assertExists(Treasury::first()->proof_photo);
    }

    public function test_proof_photo_is_required(): void
    {
        $bendahara = User::factory()->role('bendahara')->create();
        $category = TreasuryCategory::factory()->create();

        $response = $this->actingAs($bendahara, 'sanctum')->postJson(route('api.v1.treasuries.store'), [
            'treasury_category_id' => $category->id,
            'amount' => 50000,
            'transaction_date' => now()->toDateString(),
            'description' => 'Tanpa bukti',
        ]);

        $response->assertJsonValidationErrors('proof_photo');
    }
}
