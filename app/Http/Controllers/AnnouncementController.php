<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnnouncementRequest;
use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncementController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Announcements/Index', [
            'announcements' => Announcement::query()
                ->with('author:id,name')
                ->orderByDesc('publish_date')
                ->paginate(15),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Announcements/Create');
    }

    public function store(StoreAnnouncementRequest $request): RedirectResponse
    {
        Announcement::create([
            ...$request->validated(),
            'image' => $request->hasFile('image') ? $request->file('image')->store('announcements', 'public') : null,
            'created_by' => $request->user()->id,
        ]);

        return to_route('announcements.index')->with('success', 'Pengumuman berhasil dipublikasikan.');
    }

    public function edit(Announcement $announcement): Response
    {
        return Inertia::render('Announcements/Edit', [
            'announcement' => $announcement,
        ]);
    }

    public function update(StoreAnnouncementRequest $request, Announcement $announcement): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($announcement->image) {
                Storage::disk('public')->delete($announcement->image);
            }
            $data['image'] = $request->file('image')->store('announcements', 'public');
        }

        $announcement->update($data);

        return to_route('announcements.index')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        if ($announcement->image) {
            Storage::disk('public')->delete($announcement->image);
        }

        $announcement->delete();

        return to_route('announcements.index')->with('success', 'Pengumuman berhasil dihapus.');
    }
}
