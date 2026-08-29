<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreResidentRequest;
use App\Http\Requests\UpdateResidentRequest;
use App\Models\FamilyHead;
use App\Models\Resident;
use Illuminate\Http\RedirectResponse;

class ResidentController extends Controller
{
    public function store(StoreResidentRequest $request, FamilyHead $familyHead): RedirectResponse
    {
        $familyHead->residents()->create($request->validated());

        return to_route('family-heads.show', $familyHead)->with('success', 'Penduduk berhasil ditambahkan.');
    }

    public function update(UpdateResidentRequest $request, Resident $resident): RedirectResponse
    {
        $resident->update($request->validated());

        return to_route('family-heads.show', $resident->family_head_id)->with('success', 'Data penduduk berhasil diperbarui.');
    }

    public function destroy(Resident $resident): RedirectResponse
    {
        $familyHeadId = $resident->family_head_id;

        if ($resident->userAccount()->exists()) {
            return back()->with('error', 'Penduduk tidak bisa dihapus karena memiliki akun pengguna aktif.');
        }

        $resident->delete();

        return to_route('family-heads.show', $familyHeadId)->with('success', 'Data penduduk berhasil dihapus.');
    }
}
