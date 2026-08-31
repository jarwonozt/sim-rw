<?php

namespace Tests\Feature\Api\V1;

use App\Models\Complaint;
use App\Models\FamilyHead;
use App\Models\MasterRt;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComplaintTest extends TestCase
{
    use RefreshDatabase;

    private function wargaWithResident(MasterRt $rt): User
    {
        $familyHead = FamilyHead::factory()->create(['rt_id' => $rt->id]);
        $resident = Resident::factory()->create(['family_head_id' => $familyHead->id]);

        return User::factory()->role('warga')->create(['resident_id' => $resident->id]);
    }

    public function test_bendahara_cannot_access_complaints(): void
    {
        $bendahara = User::factory()->role('bendahara')->create();

        $this->actingAs($bendahara, 'sanctum')->getJson(route('api.v1.complaints.index'))->assertForbidden();
    }

    public function test_warga_can_submit_a_complaint(): void
    {
        $rt = MasterRt::factory()->create();
        $warga = $this->wargaWithResident($rt);

        $response = $this->actingAs($warga, 'sanctum')->postJson(route('api.v1.complaints.store'), [
            'title' => 'Lampu jalan mati',
            'description' => 'Lampu di depan gang sudah 3 hari mati.',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('complaints', [
            'user_id' => $warga->id,
            'rt_id' => $rt->id,
            'status' => 'menunggu_verifikasi_rt',
        ]);
    }

    public function test_warga_only_sees_their_own_complaints(): void
    {
        $rt = MasterRt::factory()->create();
        $warga = $this->wargaWithResident($rt);
        $otherWarga = $this->wargaWithResident($rt);

        Complaint::factory()->create(['user_id' => $warga->id, 'rt_id' => $rt->id]);
        Complaint::factory()->create(['user_id' => $otherWarga->id, 'rt_id' => $rt->id]);

        $response = $this->actingAs($warga, 'sanctum')->getJson(route('api.v1.complaints.index'));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function test_warga_cannot_view_another_users_complaint(): void
    {
        $rt = MasterRt::factory()->create();
        $warga = $this->wargaWithResident($rt);
        $otherWarga = $this->wargaWithResident($rt);
        $complaint = Complaint::factory()->create(['user_id' => $otherWarga->id, 'rt_id' => $rt->id]);

        $this->actingAs($warga, 'sanctum')
            ->getJson(route('api.v1.complaints.show', $complaint))
            ->assertForbidden();
    }

    public function test_ketua_rt_cannot_verify_a_complaint_in_another_rt(): void
    {
        $ketuaRt = User::factory()->role('ketua_rt')->create();
        MasterRt::factory()->create(['ketua_rt_id' => $ketuaRt->id]);
        $otherRt = MasterRt::factory()->create();
        $complaint = Complaint::factory()->create(['rt_id' => $otherRt->id, 'status' => 'menunggu_verifikasi_rt']);

        $this->actingAs($ketuaRt, 'sanctum')
            ->patchJson(route('api.v1.complaints.update-status', $complaint))
            ->assertNotFound();
    }
}
