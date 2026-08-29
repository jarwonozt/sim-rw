<?php

namespace Tests\Feature\Complaints;

use App\Models\Complaint;
use App\Models\FamilyHead;
use App\Models\MasterRt;
use App\Models\Resident;
use App\Models\User;
use App\Notifications\ComplaintResolvedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
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

        $this->actingAs($bendahara)->get(route('complaints.index'))->assertForbidden();
    }

    public function test_warga_can_submit_a_complaint(): void
    {
        $rt = MasterRt::factory()->create();
        $warga = $this->wargaWithResident($rt);

        $response = $this->actingAs($warga)->post(route('complaints.store'), [
            'title' => 'Lampu jalan mati',
            'description' => 'Lampu di depan gang sudah 3 hari mati.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('complaints', [
            'user_id' => $warga->id,
            'rt_id' => $rt->id,
            'status' => 'menunggu_verifikasi_rt',
        ]);
        $this->assertDatabaseHas('complaint_logs', [
            'status' => 'menunggu_verifikasi_rt',
            'changed_by' => $warga->id,
        ]);
    }

    public function test_warga_without_resident_link_cannot_submit(): void
    {
        $warga = User::factory()->role('warga')->create(['resident_id' => null]);

        $response = $this->actingAs($warga)->post(route('complaints.store'), [
            'title' => 'Uji coba',
            'description' => 'Deskripsi uji coba.',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('complaints', 0);
    }

    public function test_warga_only_sees_their_own_complaints(): void
    {
        $rt = MasterRt::factory()->create();
        $warga = $this->wargaWithResident($rt);
        $otherWarga = $this->wargaWithResident($rt);

        Complaint::factory()->create(['user_id' => $warga->id, 'rt_id' => $rt->id]);
        Complaint::factory()->create(['user_id' => $otherWarga->id, 'rt_id' => $rt->id]);

        $response = $this->actingAs($warga)->get(route('complaints.index'));

        $response->assertInertia(fn ($page) => $page->has('complaints.data', 1));
    }

    public function test_warga_cannot_view_another_users_complaint(): void
    {
        $rt = MasterRt::factory()->create();
        $warga = $this->wargaWithResident($rt);
        $otherWarga = $this->wargaWithResident($rt);
        $complaint = Complaint::factory()->create(['user_id' => $otherWarga->id, 'rt_id' => $rt->id]);

        $this->actingAs($warga)->get(route('complaints.show', $complaint))->assertForbidden();
    }

    public function test_ketua_rt_can_verify_a_complaint_in_their_own_rt(): void
    {
        $ketuaRt = User::factory()->role('ketua_rt')->create();
        $rt = MasterRt::factory()->create(['ketua_rt_id' => $ketuaRt->id]);
        $complaint = Complaint::factory()->create(['rt_id' => $rt->id, 'status' => 'menunggu_verifikasi_rt']);

        $response = $this->actingAs($ketuaRt)->patch(route('complaints.update-status', $complaint));

        $response->assertRedirect();
        $this->assertDatabaseHas('complaints', ['id' => $complaint->id, 'status' => 'diteruskan_rw']);
    }

    public function test_ketua_rt_cannot_verify_a_complaint_in_another_rt(): void
    {
        $ketuaRt = User::factory()->role('ketua_rt')->create();
        MasterRt::factory()->create(['ketua_rt_id' => $ketuaRt->id]);
        $otherRt = MasterRt::factory()->create();
        $complaint = Complaint::factory()->create(['rt_id' => $otherRt->id, 'status' => 'menunggu_verifikasi_rt']);

        $this->actingAs($ketuaRt)
            ->patch(route('complaints.update-status', $complaint))
            ->assertNotFound();
    }

    public function test_ketua_rt_cannot_skip_ahead_to_processing(): void
    {
        $ketuaRt = User::factory()->role('ketua_rt')->create();
        $rt = MasterRt::factory()->create(['ketua_rt_id' => $ketuaRt->id]);
        $complaint = Complaint::factory()->create(['rt_id' => $rt->id, 'status' => 'diteruskan_rw']);

        $this->actingAs($ketuaRt)
            ->patch(route('complaints.update-status', $complaint))
            ->assertForbidden();
    }

    public function test_marking_a_complaint_resolved_notifies_the_reporter(): void
    {
        Notification::fake();

        $ketuaRw = User::factory()->role('ketua_rw')->create();
        $rt = MasterRt::factory()->create();
        $warga = $this->wargaWithResident($rt);
        $complaint = Complaint::factory()->create([
            'user_id' => $warga->id,
            'rt_id' => $rt->id,
            'status' => 'proses',
        ]);

        $this->actingAs($ketuaRw)->patch(route('complaints.update-status', $complaint));

        $this->assertDatabaseHas('complaints', ['id' => $complaint->id, 'status' => 'selesai']);
        Notification::assertSentTo($warga, ComplaintResolvedNotification::class);
    }
}
