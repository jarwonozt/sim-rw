<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFamilyHeadRequest;
use App\Http\Requests\UpdateFamilyHeadRequest;
use App\Models\FamilyHead;
use App\Models\MasterRt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FamilyHeadController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();

        $familyHeads = FamilyHead::query()
            ->with('rt:id,nomor_rt,master_rw_id')
            ->withCount('residents')
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('no_kk', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            }))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('MasterData/FamilyHeads/Index', [
            'familyHeads' => $familyHeads,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('MasterData/FamilyHeads/Create', [
            'rtOptions' => $this->rtOptionsForCurrentUser($request),
        ]);
    }

    public function store(StoreFamilyHeadRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['rt_id'] = $this->resolveRtId($request, $data['rt_id']);

        $familyHead = FamilyHead::create($data);

        return to_route('family-heads.show', $familyHead)->with('success', 'Data KK berhasil ditambahkan.');
    }

    public function show(FamilyHead $familyHead): Response
    {
        $familyHead->load('rt:id,nomor_rt', 'residents');

        return Inertia::render('MasterData/FamilyHeads/Show', [
            'familyHead' => $familyHead,
        ]);
    }

    public function edit(Request $request, FamilyHead $familyHead): Response
    {
        return Inertia::render('MasterData/FamilyHeads/Edit', [
            'familyHead' => $familyHead,
            'rtOptions' => $this->rtOptionsForCurrentUser($request),
        ]);
    }

    public function update(UpdateFamilyHeadRequest $request, FamilyHead $familyHead): RedirectResponse
    {
        $data = $request->validated();
        $data['rt_id'] = $this->resolveRtId($request, $data['rt_id']);

        $familyHead->update($data);

        return to_route('family-heads.show', $familyHead)->with('success', 'Data KK berhasil diperbarui.');
    }

    public function destroy(FamilyHead $familyHead): RedirectResponse
    {
        if ($familyHead->residents()->exists()) {
            return back()->with('error', 'KK tidak bisa dihapus karena masih memiliki data penduduk.');
        }

        $familyHead->delete();

        return to_route('family-heads.index')->with('success', 'Data KK berhasil dihapus.');
    }

    /**
     * @return \Illuminate\Support\Collection<int, MasterRt>
     */
    private function rtOptionsForCurrentUser(Request $request)
    {
        $user = $request->user();

        return MasterRt::query()
            ->when($user->role === 'ketua_rt', fn ($query) => $query->where('ketua_rt_id', $user->id))
            ->orderBy('nomor_rt')
            ->get(['id', 'nomor_rt']);
    }

    /**
     * Ketua RT tidak boleh memindahkan/membuat data KK ke RT lain — abaikan
     * rt_id dari form dan paksa ke RT yang dipimpinnya (PRD Bagian 6.2).
     */
    private function resolveRtId(Request $request, int $submittedRtId): int
    {
        $user = $request->user();

        if ($user->role !== 'ketua_rt') {
            return $submittedRtId;
        }

        return MasterRt::query()->where('ketua_rt_id', $user->id)->value('id') ?? abort(403);
    }
}
