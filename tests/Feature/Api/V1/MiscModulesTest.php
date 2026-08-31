<?php

namespace Tests\Feature\Api\V1;

use App\Models\FamilyHead;
use App\Models\InventoryCategory;
use App\Models\MasterRw;
use App\Models\Resident;
use App\Models\User;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MiscModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_warga_can_view_and_update_their_own_resident_data(): void
    {
        $resident = Resident::factory()->create(['phone' => '0800000000']);
        $warga = User::factory()->role('warga')->create(['resident_id' => $resident->id]);

        $this->actingAs($warga, 'sanctum')
            ->getJson(route('api.v1.resident-profile.show'))
            ->assertOk()
            ->assertJsonPath('data.id', $resident->id);

        $response = $this->actingAs($warga, 'sanctum')->putJson(route('api.v1.resident-profile.update'), [
            'phone' => '081234567890',
            'occupation' => 'Wiraswasta',
            'education' => 'S1',
            'religion' => 'Islam',
            'marital_status' => 'Kawin',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('residents', ['id' => $resident->id, 'phone' => '081234567890']);
    }

    public function test_sekretaris_can_manage_letter_templates_but_ketua_rt_cannot(): void
    {
        $sekretaris = User::factory()->role('sekretaris')->create();
        $ketuaRt = User::factory()->role('ketua_rt')->create();

        $this->actingAs($ketuaRt, 'sanctum')->getJson(route('api.v1.letter-templates.index'))->assertForbidden();

        $response = $this->actingAs($sekretaris, 'sanctum')->postJson(route('api.v1.letter-templates.store'), [
            'name' => 'Surat Domisili',
            'type' => 'domisili',
            'content' => 'Isi surat [nama_penduduk].',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('letter_templates', ['name' => 'Surat Domisili']);
    }

    public function test_bendahara_can_manage_treasury_categories(): void
    {
        $bendahara = User::factory()->role('bendahara')->create();

        $response = $this->actingAs($bendahara, 'sanctum')->postJson(route('api.v1.treasury-categories.store'), [
            'name' => 'Iuran Warga',
            'type' => 'in',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('treasury_categories', ['name' => 'Iuran Warga']);
    }

    public function test_bendahara_can_view_the_treasury_report_summary(): void
    {
        $bendahara = User::factory()->role('bendahara')->create();

        $response = $this->actingAs($bendahara, 'sanctum')->getJson(route('api.v1.treasury-report.index'));

        $response->assertOk();
        $response->assertJsonStructure(['data' => ['summary', 'category_breakdown']]);
    }

    public function test_sekretaris_can_manage_inventory_categories_but_ketua_rt_cannot(): void
    {
        $sekretaris = User::factory()->role('sekretaris')->create();
        $ketuaRt = User::factory()->role('ketua_rt')->create();

        $this->actingAs($ketuaRt, 'sanctum')->postJson(route('api.v1.inventory-categories.store'), [
            'name' => 'Elektronik',
        ])->assertForbidden();

        $response = $this->actingAs($sekretaris, 'sanctum')->postJson(route('api.v1.inventory-categories.store'), [
            'name' => 'Elektronik',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('inventory_categories', ['name' => 'Elektronik']);
    }

    public function test_ketua_rw_can_view_the_inventory_report(): void
    {
        $ketuaRw = User::factory()->role('ketua_rw')->create();
        InventoryCategory::factory()->create();

        $response = $this->actingAs($ketuaRw, 'sanctum')->getJson(route('api.v1.inventory-report.index'));

        $response->assertOk();
        $response->assertJsonStructure(['data' => ['summary', 'by_category', 'recent_loans']]);
    }

    public function test_ketua_rw_can_manage_whatsapp_templates(): void
    {
        $ketuaRw = User::factory()->role('ketua_rw')->create();

        $response = $this->actingAs($ketuaRw, 'sanctum')->postJson(route('api.v1.whatsapp-templates.store'), [
            'name' => 'Notifikasi Umum',
            'content' => 'Halo [nama_penduduk]',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('whatsapp_templates', ['name' => 'Notifikasi Umum']);
    }

    public function test_sekretaris_can_search_residents(): void
    {
        $sekretaris = User::factory()->role('sekretaris')->create();
        $familyHead = FamilyHead::factory()->create();
        Resident::factory()->create(['family_head_id' => $familyHead->id, 'name' => 'Budi Santoso']);

        $response = $this->actingAs($sekretaris, 'sanctum')->getJson(route('api.v1.residents.search', ['q' => 'Budi']));

        $response->assertOk();
        $response->assertJsonFragment(['name' => 'Budi Santoso']);
    }

    public function test_ketua_rw_can_view_and_update_the_rw_profile(): void
    {
        $ketuaRw = User::factory()->role('ketua_rw')->create();
        $village = Village::factory()->create();
        $rw = MasterRw::factory()->create(['village_id' => $village->id]);

        $this->actingAs($ketuaRw, 'sanctum')
            ->getJson(route('api.v1.rw.show'))
            ->assertOk()
            ->assertJsonPath('data.id', $rw->id);

        $response = $this->actingAs($ketuaRw, 'sanctum')->putJson(route('api.v1.rw.update'), [
            'village_id' => $village->id,
            'nomor_rw' => '007',
            'address' => 'Jl. Merdeka',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('master_rw', ['nomor_rw' => '007']);
    }
}
