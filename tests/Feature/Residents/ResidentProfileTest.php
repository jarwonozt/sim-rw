<?php

namespace Tests\Feature\Residents;

use App\Models\FamilyHead;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResidentProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_sekretaris_cannot_access_the_self_service_page(): void
    {
        $sekretaris = User::factory()->role('sekretaris')->create();

        $this->actingAs($sekretaris)->get(route('resident-profile.edit'))->assertForbidden();
    }

    public function test_warga_without_a_linked_resident_sees_a_friendly_message(): void
    {
        $warga = User::factory()->role('warga')->create(['resident_id' => null]);

        $response = $this->actingAs($warga)->get(route('resident-profile.edit'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->where('resident', null));
    }

    public function test_warga_can_view_their_own_resident_data(): void
    {
        $familyHead = FamilyHead::factory()->create(['no_kk' => '3171011234560099']);
        $resident = Resident::factory()->create([
            'family_head_id' => $familyHead->id,
            'name' => 'Budi Santoso',
        ]);
        $warga = User::factory()->role('warga')->create(['resident_id' => $resident->id]);

        $response = $this->actingAs($warga)->get(route('resident-profile.edit'));

        $response->assertInertia(fn ($page) => $page
            ->where('resident.name', 'Budi Santoso')
            ->where('resident.family_head.no_kk', '3171011234560099')
        );
    }

    public function test_warga_can_update_their_own_contact_fields(): void
    {
        $resident = Resident::factory()->create(['phone' => '0800000000']);
        $warga = User::factory()->role('warga')->create(['resident_id' => $resident->id]);

        $response = $this->actingAs($warga)->put(route('resident-profile.update'), [
            'phone' => '081234567890',
            'occupation' => 'Wiraswasta',
            'education' => 'S1',
            'religion' => 'Islam',
            'marital_status' => 'Kawin',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('residents', [
            'id' => $resident->id,
            'phone' => '081234567890',
            'occupation' => 'Wiraswasta',
        ]);
    }

    public function test_warga_cannot_change_identity_fields_via_the_self_service_endpoint(): void
    {
        $resident = Resident::factory()->create(['nik' => '3171019912340001', 'name' => 'Nama Asli']);
        $warga = User::factory()->role('warga')->create(['resident_id' => $resident->id]);

        $this->actingAs($warga)->put(route('resident-profile.update'), [
            'nik' => '9999999999999999',
            'name' => 'Nama Palsu',
            'phone' => '081200000000',
        ]);

        $this->assertDatabaseHas('residents', [
            'id' => $resident->id,
            'nik' => '3171019912340001',
            'name' => 'Nama Asli',
            'phone' => '081200000000',
        ]);
    }

    public function test_warga_without_a_linked_resident_cannot_submit_an_update(): void
    {
        $warga = User::factory()->role('warga')->create(['resident_id' => null]);

        $response = $this->actingAs($warga)->put(route('resident-profile.update'), [
            'phone' => '081234567890',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('residents', 0);
    }
}
