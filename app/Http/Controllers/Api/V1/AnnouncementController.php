<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnnouncementRequest;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $announcements = Announcement::query()
            ->with('author:id,name')
            ->when($request->user()->role === 'warga', fn ($query) => $query
                ->whereDate('publish_date', '<=', now())
                ->where(fn ($query) => $query->whereNull('expire_date')->orWhereDate('expire_date', '>=', now()))
            )
            ->orderByDesc('publish_date')
            ->paginate(15);

        return response()->json(AnnouncementResource::collection($announcements)->response()->getData(true));
    }

    public function store(StoreAnnouncementRequest $request): JsonResponse
    {
        $announcement = Announcement::create([
            ...$request->validated(),
            'image' => $request->hasFile('image') ? $request->file('image')->store('announcements', 'public') : null,
            'created_by' => $request->user()->id,
        ]);

        return response()->json(['data' => new AnnouncementResource($announcement)], 201);
    }

    public function update(StoreAnnouncementRequest $request, Announcement $announcement): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($announcement->image) {
                Storage::disk('public')->delete($announcement->image);
            }
            $data['image'] = $request->file('image')->store('announcements', 'public');
        }

        $announcement->update($data);

        return response()->json(['data' => new AnnouncementResource($announcement)]);
    }

    public function destroy(Announcement $announcement): JsonResponse
    {
        if ($announcement->image) {
            Storage::disk('public')->delete($announcement->image);
        }

        $announcement->delete();

        return response()->json(['message' => 'Pengumuman berhasil dihapus.']);
    }
}
