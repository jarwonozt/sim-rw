<?php

namespace Tests\Feature\MasterData;

use App\Models\FamilyHead;
use App\Models\MasterRt;
use App\Models\MasterRw;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FamilyHeadTest extends TestCase
{
    use RefreshDatabase;

    public function test_bendahara_cannot_access_family_heads(): void
    {
        $bendahara = User::factory()->role('bendahara')->create();

        $this->actingAs($bendahara)->get(route('family-heads.index'))->assertForbidden();
    }

    public function test_warga_cannot_access_family_heads(): void
    {
        $warga = User::factory()->role('warga')->create();

        $this->actingAs($warga)->get(route('family-heads.index'))->assertForbidden();
    }

    public function test_sekretaris_can_create_a_family_head(): void
    {
        $sekretaris = User::factory()->role('sekretaris')->create();
        $rt = MasterRt::factory()->create();

        $response = $this->actingAs($sekretaris)->post(route('family-heads.store'), [
            'rt_id' => $rt->id,
            'no_kk' => '3171011234560001',
            'address' => 'Jl. Merdeka No. 1',
            'postal_code' => '12345',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('family_heads', [
            'no_kk' => '3171011234560001',
            'rt_id' => $rt->id,
        ]);
    }

    public function test_no_kk_must_be_16_digits(): void
    {
        $sekretaris = User::factory()->role('sekretaris')->create();
        $rt = MasterRt::factory()->create();

        $response = $this->actingAs($sekretaris)->post(route('family-heads.store'), [
            'rt_id' => $rt->id,
            'no_kk' => '12345',
            'address' => 'Jl. Merdeka No. 1',
        ]);

        $response->assertSessionHasErrors('no_kk');
    }

    public function test_ketua_rt_only_sees_family_heads_in_their_own_rt(): void
    {
        $rw = MasterRw::factory()->create();

        $ketuaRt1 = User::factory()->role('ketua_rt')->create();
        $rt1 = MasterRt::factory()->create(['master_rw_id' => $rw->id, 'ketua_rt_id' => $ketuaRt1->id]);
        $rt2 = MasterRt::factory()->create(['master_rw_id' => $rw->id]);

        FamilyHead::factory(3)->create(['rt_id' => $rt1->id]);
        FamilyHead::factory(2)->create(['rt_id' => $rt2->id]);

        $response = $this->actingAs($ketuaRt1)->get(route('family-heads.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->has('familyHeads.data', 3));
    }

    public function test_ketua_rt_cannot_view_family_head_from_another_rt(): void
    {
        $ketuaRt1 = User::factory()->role('ketua_rt')->create();
        $rt1 = MasterRt::factory()->create(['ketua_rt_id' => $ketuaRt1->id]);
        $rt2 = MasterRt::factory()->create();

        $familyHead = FamilyHead::factory()->create(['rt_id' => $rt2->id]);

        $this->actingAs($ketuaRt1)
            ->get(route('family-heads.show', $familyHead))
            ->assertNotFound();
    }

    public function test_ketua_rt_cannot_assign_family_head_to_another_rt(): void
    {
        $ketuaRt1 = User::factory()->role('ketua_rt')->create();
        MasterRt::factory()->create(['ketua_rt_id' => $ketuaRt1->id]);
        $otherRt = MasterRt::factory()->create();

        $response = $this->actingAs($ketuaRt1)->post(route('family-heads.store'), [
            'rt_id' => $otherRt->id,
            'no_kk' => '3171011234560099',
            'address' => 'Jl. Coba No. 9',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('family_heads', ['rt_id' => $otherRt->id]);
    }
}
