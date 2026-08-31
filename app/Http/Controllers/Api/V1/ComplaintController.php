<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreComplaintRequest;
use App\Http\Requests\UpdateComplaintStatusRequest;
use App\Http\Resources\ComplaintResource;
use App\Models\Complaint;
use App\Models\MasterRt;
use App\Models\User;
use App\Notifications\ComplaintResolvedNotification;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    /**
     * Alur status linear (FR05.2), sama persis dengan `ComplaintController` web.
     *
     * @var array<string, array{next: string, roles: array<int, string>}>
     */
    private const TRANSITIONS = [
        'menunggu_verifikasi_rt' => ['next' => 'diteruskan_rw', 'roles' => ['ketua_rt', 'ketua_rw', 'super_admin']],
        'diteruskan_rw' => ['next' => 'proses', 'roles' => ['ketua_rw', 'super_admin']],
        'proses' => ['next' => 'selesai', 'roles' => ['ketua_rw', 'super_admin']],
    ];

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $complaints = Complaint::query()
            ->with('user:id,name', 'rt:id,nomor_rt')
            ->when($user->role === 'warga', fn ($query) => $query->where('user_id', $user->id))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return response()->json(ComplaintResource::collection($complaints)->response()->getData(true));
    }

    public function store(StoreComplaintRequest $request): JsonResponse
    {
        $user = $request->user();
        $rt = $this->rtForUser($user);

        if (! $rt) {
            return response()->json([
                'message' => 'Akun Anda belum terhubung dengan data penduduk/RT. Hubungi pengurus RW.',
            ], 422);
        }

        $complaint = Complaint::create([
            ...$request->validated(),
            'user_id' => $user->id,
            'rt_id' => $rt->id,
            'photo' => $request->hasFile('photo') ? $request->file('photo')->store('complaints', 'public') : null,
            'status' => 'menunggu_verifikasi_rt',
        ]);

        $complaint->logs()->create([
            'status' => 'menunggu_verifikasi_rt',
            'changed_by' => $user->id,
        ]);

        return response()->json(['data' => new ComplaintResource($complaint)], 201);
    }

    public function show(Request $request, Complaint $complaint): JsonResponse
    {
        $user = $request->user();

        if ($user->role === 'warga' && $complaint->user_id !== $user->id) {
            abort(403);
        }

        $complaint->load('user:id,name', 'rt:id,nomor_rt', 'logs.changedBy:id,name');

        return response()->json([
            'data' => new ComplaintResource($complaint),
            'meta' => ['next_transition' => self::TRANSITIONS[$complaint->status] ?? null],
        ]);
    }

    public function updateStatus(UpdateComplaintStatusRequest $request, Complaint $complaint): JsonResponse
    {
        $transition = self::TRANSITIONS[$complaint->status] ?? null;
        $user = $request->user();

        if (! $transition || ! in_array($user->role, $transition['roles'], true)) {
            abort(403, 'Anda tidak berwenang mengubah status pengaduan ini.');
        }

        $nextStatus = $transition['next'];

        $complaint->update(['status' => $nextStatus]);

        $complaint->logs()->create([
            'status' => $nextStatus,
            'note' => $request->validated('note'),
            'changed_by' => $user->id,
        ]);

        if ($nextStatus === 'selesai') {
            $complaint->user->notify(new ComplaintResolvedNotification($complaint));
        }

        ActivityLogger::log('complaint.status_updated', "Mengubah status pengaduan \"{$complaint->title}\" menjadi \"{$nextStatus}\".");

        return response()->json(['data' => new ComplaintResource($complaint)]);
    }

    private function rtForUser(User $user): ?MasterRt
    {
        return $user->resident?->familyHead?->rt;
    }
}
