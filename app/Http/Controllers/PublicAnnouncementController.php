<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Arsip pengumuman publik (FR06.2) — warga tidak perlu login untuk melihat.
 */
class PublicAnnouncementController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Public/Announcements/Index', [
            'announcements' => $this->publishedQuery()
                ->orderByDesc('publish_date')
                ->paginate(10),
        ]);
    }

    public function show(Announcement $announcement): Response
    {
        abort_unless(
            $this->publishedQuery()->whereKey($announcement->id)->exists(),
            404
        );

        return Inertia::render('Public/Announcements/Show', [
            'announcement' => $announcement,
        ]);
    }

    private function publishedQuery(): Builder
    {
        $today = Carbon::today();

        return Announcement::query()
            ->where('publish_date', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('expire_date')->orWhere('expire_date', '>=', $today);
            });
    }
}
