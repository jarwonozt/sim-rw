<?php

namespace Tests\Feature\Api\V1;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    public function test_sekretaris_cannot_manage_announcements(): void
    {
        $sekretaris = User::factory()->role('sekretaris')->create();

        $this->actingAs($sekretaris, 'sanctum')->getJson(route('api.v1.announcements.index'))->assertForbidden();
    }

    public function test_ketua_rw_can_publish_an_announcement(): void
    {
        $ketuaRw = User::factory()->role('ketua_rw')->create();

        $response = $this->actingAs($ketuaRw, 'sanctum')->postJson(route('api.v1.announcements.store'), [
            'title' => 'Kerja Bakti Akbar',
            'content' => 'Kerja bakti akan dilaksanakan hari Minggu pukul 07.00.',
            'publish_date' => now()->toDateString(),
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('announcements', [
            'title' => 'Kerja Bakti Akbar',
            'created_by' => $ketuaRw->id,
        ]);
    }

    public function test_warga_only_sees_published_and_unexpired_announcements(): void
    {
        $warga = User::factory()->role('warga')->create();

        Announcement::factory()->create([
            'title' => 'Tayang',
            'publish_date' => now()->subDay(),
            'expire_date' => now()->addDay(),
        ]);
        Announcement::factory()->create([
            'title' => 'Belum Tayang',
            'publish_date' => now()->addDay(),
            'expire_date' => null,
        ]);
        Announcement::factory()->create([
            'title' => 'Kadaluarsa',
            'publish_date' => now()->subDays(10),
            'expire_date' => now()->subDay(),
        ]);

        $response = $this->actingAs($warga, 'sanctum')->getJson(route('api.v1.announcements.index'));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.title', 'Tayang');
    }

    public function test_warga_cannot_create_an_announcement(): void
    {
        $warga = User::factory()->role('warga')->create();

        $this->actingAs($warga, 'sanctum')->postJson(route('api.v1.announcements.store'), [
            'title' => 'Coba',
            'content' => 'Coba isi',
            'publish_date' => now()->toDateString(),
        ])->assertForbidden();
    }
}
