<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateOwnResidentRequest;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Halaman "Data Saya" — warga melihat & memperbarui data kependudukan
 * miliknya sendiri (kontak/non-identitas saja, lihat UpdateOwnResidentRequest).
 */
class ResidentProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        $resident = $request->user()->resident()->with('familyHead.rt')->first();

        return Inertia::render('ResidentProfile/Edit', [
            'resident' => $resident,
        ]);
    }

    public function update(UpdateOwnResidentRequest $request): RedirectResponse
    {
        $resident = $request->user()->resident;

        if (! $resident) {
            return back()->with('error', 'Akun Anda belum terhubung dengan data penduduk.');
        }

        $resident->update($request->validated());

        ActivityLogger::log('resident.self_updated', "{$request->user()->name} memperbarui data kependudukannya sendiri.");

        return back()->with('success', 'Data Anda berhasil diperbarui.');
    }
}
