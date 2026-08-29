<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateMasterRwRequest;
use App\Models\MasterRw;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Profil RW bersifat singleton untuk satu instalasi SIM-RW (lihat catatan
 * desain di docs/erd.md) sehingga hanya disediakan edit/update, bukan
 * resource CRUD penuh.
 */
class MasterRwController extends Controller
{
    public function edit(): Response
    {
        $rw = MasterRw::with('village.subdistrict.district.province')->first();

        return Inertia::render('MasterData/Rw/Edit', [
            'rw' => $rw,
            'ketuaRwOptions' => User::query()->where('role', 'ketua_rw')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateMasterRwRequest $request): RedirectResponse
    {
        $rw = MasterRw::query()->first();

        if ($rw) {
            $rw->update($request->validated());
        } else {
            MasterRw::create($request->validated());
        }

        return to_route('rw.edit')->with('success', 'Profil RW berhasil disimpan.');
    }
}
