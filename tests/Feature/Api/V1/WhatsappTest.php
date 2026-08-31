<?php

namespace Tests\Feature\Api\V1;

use App\Models\FamilyHead;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsappTest extends TestCase
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

        $this->actingAs($bendahara, 'sanctum')->getJson(route('api.v1.whatsapp-broadcast.index'))->assertForbidden();
    }

    public function test_ketua_rw_can_broadcast_to_all_residents_with_a_phone(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true], 200)]);

        $ketuaRw = User::factory()->role('ketua_rw')->create();
        $familyHead = FamilyHead::factory()->create();
        Resident::factory()->create(['family_head_id' => $familyHead->id, 'phone' => '081111111111']);
        Resident::factory()->create(['family_head_id' => $familyHead->id, 'phone' => '081222222222']);

        $response = $this->actingAs($ketuaRw, 'sanctum')->postJson(route('api.v1.whatsapp-broadcast.store'), [
            'message' => 'Ada kerja bakti hari Minggu.',
        ]);

        $response->assertCreated();
        Http::assertSentCount(2);
        $this->assertDatabaseHas('whatsapp_broadcasts', [
            'recipients_count' => 2,
            'success_count' => 2,
        ]);
    }
}
