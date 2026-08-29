<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMasterRtRequest;
use App\Http\Requests\UpdateMasterRtRequest;
use App\Models\MasterRt;
use App\Models\MasterRw;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MasterRtController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('MasterData/Rt/Index', [
            'rts' => MasterRt::query()
                ->withCount('familyHeads')
                ->with('ketuaRt:id,name')
                ->orderBy('nomor_rt')
                ->get(),
            'ketuaRtOptions' => User::query()->where('role', 'ketua_rt')->get(['id', 'name']),
        ]);
    }

    public function store(StoreMasterRtRequest $request): RedirectResponse
    {
        $rw = MasterRw::query()->firstOrFail();

        $rw->rts()->create($request->validated());

        return to_route('rt.index')->with('success', 'RT berhasil ditambahkan.');
    }

    public function update(UpdateMasterRtRequest $request, MasterRt $rt): RedirectResponse
    {
        $rt->update($request->validated());

        return to_route('rt.index')->with('success', 'RT berhasil diperbarui.');
    }

    public function destroy(MasterRt $rt): RedirectResponse
    {
        if ($rt->familyHeads()->exists()) {
            return to_route('rt.index')->with('error', 'RT tidak bisa dihapus karena masih memiliki data KK.');
        }

        $rt->delete();

        return to_route('rt.index')->with('success', 'RT berhasil dihapus.');
    }
}
