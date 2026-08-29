<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreComplaintRequest;
use App\Http\Requests\UpdateComplaintStatusRequest;
use App\Models\Complaint;
use App\Models\MasterRt;
use App\Models\User;
use App\Notifications\ComplaintResolvedNotification;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ComplaintController extends Controller
{
    /**
     * Alur status linear (FR05.2): setiap status hanya boleh maju ke status
     * berikutnya, dan hanya oleh role yang berwenang di tahap itu.
     *
     * @var array<string, array{next: string, roles: array<int, string>}>
     */
    private const TRANSITIONS = [
        'menunggu_verifikasi_rt' => ['next' => 'diteruskan_rw', 'roles' => ['ketua_rt', 'ketua_rw', 'super_admin']],
        'diteruskan_rw' => ['next' => 'proses', 'roles' => ['ketua_rw', 'super_admin']],
        'proses' => ['next' => 'selesai', 'roles' => ['ketua_rw', 'super_admin']],
    ];

    public function index(Request $request): Response
    {
        $user = $request->user();

        $complaints = Complaint::query()
            ->with('user:id,name', 'rt:id,nomor_rt')
            ->when($user->role === 'warga', fn ($query) => $query->where('user_id', $user->id))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Complaints/Index', [
            'complaints' => $complaints,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Complaints/Create');
    }

    public function store(StoreComplaintRequest $request): RedirectResponse
    {
        $user = $request->user();
        $rt = $this->rtForUser($user);

        if (! $rt) {
            return back()->with('error', 'Akun Anda belum terhubung dengan data penduduk/RT. Hubungi pengurus RW.');
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

        return to_route('complaints.show', $complaint)->with('success', 'Pengaduan berhasil dikirim.');
    }

    public function show(Request $request, Complaint $complaint): Response
    {
        $user = $request->user();

        if ($user->role === 'warga' && $complaint->user_id !== $user->id) {
            abort(403);
        }

        $complaint->load('user:id,name', 'rt:id,nomor_rt', 'logs.changedBy:id,name');

        return Inertia::render('Complaints/Show', [
            'complaint' => $complaint,
            'nextTransition' => self::TRANSITIONS[$complaint->status] ?? null,
        ]);
    }

    public function updateStatus(UpdateComplaintStatusRequest $request, Complaint $complaint): RedirectResponse
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

        return back()->with('success', 'Status pengaduan berhasil diperbarui.');
    }

    private function rtForUser(User $user): ?MasterRt
    {
        return $user->resident?->familyHead?->rt;
    }
}
