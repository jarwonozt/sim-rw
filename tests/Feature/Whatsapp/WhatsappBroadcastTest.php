<?php

namespace Tests\Feature\Whatsapp;

use App\Models\FamilyHead;
use App\Models\MasterRt;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsappBroadcastTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.fonnte.token' => 'test-token']);
    }

    public function test_bendahara_cannot_access_broadcast(): void
    {
        $bendahara = User::factory()->role('bendahara')->create();

        $this->actingAs($bendahara)->get(route('whatsapp-broadcast.index'))->assertForbidden();
    }

    public function test_ketua_rw_can_broadcast_to_all_residents_with_a_phone(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true], 200)]);

        $ketuaRw = User::factory()->role('ketua_rw')->create();
        $familyHead = FamilyHead::factory()->create();
        Resident::factory()->create(['family_head_id' => $familyHead->id, 'phone' => '081111111111']);
        Resident::factory()->create(['family_head_id' => $familyHead->id, 'phone' => '081222222222']);
        Resident::factory()->create(['family_head_id' => $familyHead->id, 'phone' => null]);

        $response = $this->actingAs($ketuaRw)->post(route('whatsapp-broadcast.store'), [
            'message' => 'Ada kerja bakti hari Minggu.',
        ]);

        $response->assertRedirect(route('whatsapp-broadcast.index'));
        Http::assertSentCount(2);
        $this->assertDatabaseHas('whatsapp_broadcasts', [
            'recipients_count' => 2,
            'success_count' => 2,
            'failed_count' => 0,
        ]);
    }

    public function test_broadcast_can_be_targeted_to_a_single_rt(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true], 200)]);

        $ketuaRw = User::factory()->role('ketua_rw')->create();
        $rt1 = MasterRt::factory()->create();
        $rt2 = MasterRt::factory()->create();
        $familyHead1 = FamilyHead::factory()->create(['rt_id' => $rt1->id]);
        $familyHead2 = FamilyHead::factory()->create(['rt_id' => $rt2->id]);
        Resident::factory()->create(['family_head_id' => $familyHead1->id, 'phone' => '081111111111']);
        Resident::factory()->create(['family_head_id' => $familyHead2->id, 'phone' => '081222222222']);

        $this->actingAs($ketuaRw)->post(route('whatsapp-broadcast.store'), [
            'rt_id' => $rt1->id,
            'message' => 'Khusus RT ini.',
        ]);

        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request['target'] === '6281111111111');
    }

    public function test_duplicate_phone_numbers_are_only_messaged_once(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true], 200)]);

        $ketuaRw = User::factory()->role('ketua_rw')->create();
        $familyHead = FamilyHead::factory()->create();
        Resident::factory()->create(['family_head_id' => $familyHead->id, 'phone' => '081111111111']);
        Resident::factory()->create(['family_head_id' => $familyHead->id, 'phone' => '081111111111']);

        $this->actingAs($ketuaRw)->post(route('whatsapp-broadcast.store'), [
            'message' => 'Halo semua.',
        ]);

        Http::assertSentCount(1);
    }

    public function test_broadcast_with_no_eligible_recipients_shows_an_error(): void
    {
        $ketuaRw = User::factory()->role('ketua_rw')->create();

        $response = $this->actingAs($ketuaRw)->post(route('whatsapp-broadcast.store'), [
            'message' => 'Tidak ada penerima.',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('whatsapp_broadcasts', 0);
    }
}
