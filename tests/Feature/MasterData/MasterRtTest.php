<?php

namespace Tests\Feature\MasterData;

use App\Models\FamilyHead;
use App\Models\MasterRt;
use App\Models\MasterRw;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterRtTest extends TestCase
{
    use RefreshDatabase;

    public function test_sekretaris_cannot_manage_rt(): void
    {
        $sekretaris = User::factory()->role('sekretaris')->create();

        $this->actingAs($sekretaris)->get(route('rt.index'))->assertForbidden();
    }

    public function test_ketua_rw_can_create_rt(): void
    {
        $ketuaRw = User::factory()->role('ketua_rw')->create();
        $rw = MasterRw::factory()->create();

        $response = $this->actingAs($ketuaRw)->post(route('rt.store'), [
            'nomor_rt' => '005',
        ]);

        $response->assertRedirect(route('rt.index'));
        $this->assertDatabaseHas('master_rt', [
            'nomor_rt' => '005',
            'master_rw_id' => $rw->id,
        ]);
    }

    public function test_rt_with_family_heads_cannot_be_deleted(): void
    {
        $ketuaRw = User::factory()->role('ketua_rw')->create();
        $rt = MasterRt::factory()->create();
        FamilyHead::factory()->create(['rt_id' => $rt->id]);

        $this->actingAs($ketuaRw)->delete(route('rt.destroy', $rt));

        $this->assertDatabaseHas('master_rt', ['id' => $rt->id]);
    }

    public function test_rt_without_family_heads_can_be_deleted(): void
    {
        $ketuaRw = User::factory()->role('ketua_rw')->create();
        $rt = MasterRt::factory()->create();

        $this->actingAs($ketuaRw)->delete(route('rt.destroy', $rt));

        $this->assertDatabaseMissing('master_rt', ['id' => $rt->id]);
    }
}
