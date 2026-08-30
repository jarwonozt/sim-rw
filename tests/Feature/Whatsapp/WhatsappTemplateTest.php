<?php

namespace Tests\Feature\Whatsapp;

use App\Models\Complaint;
use App\Models\FamilyHead;
use App\Models\MasterRt;
use App\Models\Resident;
use App\Models\User;
use App\Models\WhatsappTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsappTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_sekretaris_cannot_manage_whatsapp_templates(): void
    {
        $sekretaris = User::factory()->role('sekretaris')->create();

        $this->actingAs($sekretaris)->get(route('whatsapp-templates.index'))->assertForbidden();
    }

    public function test_ketua_rw_can_create_a_template(): void
    {
        $ketuaRw = User::factory()->role('ketua_rw')->create();

        $response = $this->actingAs($ketuaRw)->post(route('whatsapp-templates.store'), [
            'name' => 'Notifikasi Selesai',
            'event_key' => 'complaint_resolved',
            'content' => 'Halo [nama_warga], pengaduan [judul_pengaduan] sudah selesai.',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('whatsapp-templates.index'));
        $this->assertDatabaseHas('whatsapp_templates', [
            'name' => 'Notifikasi Selesai',
            'event_key' => 'complaint_resolved',
        ]);
    }

    public function test_event_key_must_be_unique_across_templates(): void
    {
        $ketuaRw = User::factory()->role('ketua_rw')->create();
        WhatsappTemplate::factory()->create(['event_key' => 'complaint_resolved']);

        $response = $this->actingAs($ketuaRw)->post(route('whatsapp-templates.store'), [
            'name' => 'Duplikat',
            'event_key' => 'complaint_resolved',
            'content' => 'Isi pesan.',
        ]);

        $response->assertSessionHasErrors('event_key');
    }

    public function test_active_template_is_used_for_the_complaint_resolved_notification(): void
    {
        config(['services.fonnte.token' => 'test-token']);
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true], 200)]);

        WhatsappTemplate::factory()->create([
            'event_key' => 'complaint_resolved',
            'content' => 'Halo [nama_warga]! Laporan "[judul_pengaduan]" TUNTAS.',
            'is_active' => true,
        ]);

        $ketuaRw = User::factory()->role('ketua_rw')->create();
        $rt = MasterRt::factory()->create();
        $familyHead = FamilyHead::factory()->create(['rt_id' => $rt->id]);
        $resident = Resident::factory()->create(['family_head_id' => $familyHead->id]);
        $warga = User::factory()->role('warga')->create([
            'name' => 'Citra',
            'resident_id' => $resident->id,
        ]);
        $complaint = Complaint::factory()->create([
            'user_id' => $warga->id,
            'rt_id' => $rt->id,
            'title' => 'Got bocor',
            'status' => 'proses',
        ]);

        $this->actingAs($ketuaRw)->patch(route('complaints.update-status', $complaint));

        Http::assertSent(fn ($request) => $request['message'] === 'Halo Citra! Laporan "Got bocor" TUNTAS.');
    }
}
