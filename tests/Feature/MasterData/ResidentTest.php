<?php

namespace Tests\Feature\MasterData;

use App\Models\FamilyHead;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResidentTest extends TestCase
{
    use RefreshDatabase;

    public function test_sekretaris_can_add_a_resident_to_a_family_head(): void
    {
        $sekretaris = User::factory()->role('sekretaris')->create();
        $familyHead = FamilyHead::factory()->create();

        $response = $this->actingAs($sekretaris)->post(route('residents.store', $familyHead), [
            'nik' => '3171012345670001',
            'name' => 'Budi Santoso',
            'gender' => 'L',
            'is_family_head' => true,
            'relationship_status' => 'Kepala Keluarga',
        ]);

        $response->assertRedirect(route('family-heads.show', $familyHead));
        $this->assertDatabaseHas('residents', [
            'nik' => '3171012345670001',
            'family_head_id' => $familyHead->id,
        ]);
    }

    public function test_nik_must_be_unique(): void
    {
        $sekretaris = User::factory()->role('sekretaris')->create();
        $familyHead = FamilyHead::factory()->create();
        Resident::factory()->create(['nik' => '3171012345670099']);

        $response = $this->actingAs($sekretaris)->post(route('residents.store', $familyHead), [
            'nik' => '3171012345670099',
            'name' => 'Duplikat NIK',
            'gender' => 'L',
        ]);

        $response->assertSessionHasErrors('nik');
    }

    public function test_bendahara_cannot_add_a_resident(): void
    {
        $bendahara = User::factory()->role('bendahara')->create();
        $familyHead = FamilyHead::factory()->create();

        $this->actingAs($bendahara)
            ->post(route('residents.store', $familyHead), [
                'nik' => '3171012345670002',
                'name' => 'Tidak Boleh',
                'gender' => 'P',
            ])
            ->assertForbidden();
    }
}
