<?php

namespace Tests\Feature\Announcements;

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

        $this->actingAs($sekretaris)->get(route('announcements.index'))->assertForbidden();
    }

    public function test_ketua_rw_can_publish_an_announcement(): void
    {
        $ketuaRw = User::factory()->role('ketua_rw')->create();

        $response = $this->actingAs($ketuaRw)->post(route('announcements.store'), [
            'title' => 'Kerja Bakti Akbar',
            'content' => 'Kerja bakti akan dilaksanakan hari Minggu pukul 07.00.',
            'publish_date' => now()->toDateString(),
        ]);

        $response->assertRedirect(route('announcements.index'));
        $this->assertDatabaseHas('announcements', [
            'title' => 'Kerja Bakti Akbar',
            'created_by' => $ketuaRw->id,
        ]);
    }

    public function test_guests_can_view_published_announcements_without_logging_in(): void
    {
        Announcement::factory()->create([
            'title' => 'Pengumuman Publik',
            'publish_date' => now()->subDay(),
            'expire_date' => now()->addDay(),
        ]);

        $response = $this->get(route('public-announcements.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->has('announcements.data', 1));
    }

    public function test_guests_do_not_see_expired_or_unpublished_announcements(): void
    {
        Announcement::factory()->create([
            'title' => 'Sudah Kadaluarsa',
            'publish_date' => now()->subDays(10),
            'expire_date' => now()->subDay(),
        ]);
        Announcement::factory()->create([
            'title' => 'Belum Tayang',
            'publish_date' => now()->addDay(),
            'expire_date' => null,
        ]);

        $response = $this->get(route('public-announcements.index'));

        $response->assertInertia(fn ($page) => $page->has('announcements.data', 0));
    }

    public function test_guest_cannot_view_an_unpublished_announcement_directly(): void
    {
        $announcement = Announcement::factory()->create([
            'publish_date' => now()->addDay(),
        ]);

        $this->get(route('public-announcements.show', $announcement))->assertNotFound();
    }
}
