<?php

namespace Tests\Feature\Api\V1;

use App\Models\FamilyHead;
use App\Models\MasterRt;
use App\Models\MasterRw;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_sekretaris_cannot_manage_rt(): void
    {
        $sekretaris = User::factory()->role('sekretaris')->create();

        $this->actingAs($sekretaris, 'sanctum')->getJson(route('api.v1.rt.index'))->assertForbidden();
    }

    public function test_ketua_rw_can_create_an_rt(): void
    {
        $ketuaRw = User::factory()->role('ketua_rw')->create();
        $rw = MasterRw::factory()->create();

        $response = $this->actingAs($ketuaRw, 'sanctum')->postJson(route('api.v1.rt.store'), [
            'nomor_rt' => '005',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('master_rt', ['nomor_rt' => '005', 'master_rw_id' => $rw->id]);
    }

    public function test_bendahara_cannot_access_family_heads(): void
    {
        $bendahara = User::factory()->role('bendahara')->create();

        $this->actingAs($bendahara, 'sanctum')->getJson(route('api.v1.family-heads.index'))->assertForbidden();
    }

    public function test_ketua_rt_only_sees_family_heads_in_their_own_rt(): void
    {
        $rw = MasterRw::factory()->create();
        $ketuaRt = User::factory()->role('ketua_rt')->create();
        $rt1 = MasterRt::factory()->create(['master_rw_id' => $rw->id, 'ketua_rt_id' => $ketuaRt->id]);
        $rt2 = MasterRt::factory()->create(['master_rw_id' => $rw->id]);

        FamilyHead::factory(3)->create(['rt_id' => $rt1->id]);
        FamilyHead::factory(2)->create(['rt_id' => $rt2->id]);

        $response = $this->actingAs($ketuaRt, 'sanctum')->getJson(route('api.v1.family-heads.index'));

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
    }

    public function test_ketua_rt_cannot_view_a_family_head_from_another_rt(): void
    {
        $ketuaRt = User::factory()->role('ketua_rt')->create();
        MasterRt::factory()->create(['ketua_rt_id' => $ketuaRt->id]);
        $otherRt = MasterRt::factory()->create();
        $familyHead = FamilyHead::factory()->create(['rt_id' => $otherRt->id]);

        $this->actingAs($ketuaRt, 'sanctum')
            ->getJson(route('api.v1.family-heads.show', $familyHead))
            ->assertNotFound();
    }
}
