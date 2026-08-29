<?php

namespace App\Http\Controllers;

use App\Models\Resident;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Pencarian cepat penduduk (by NIK/nama) untuk combobox di form Surat.
 * Hasil otomatis dibatasi ke RT milik Ketua RT lewat global scope Resident.
 */
class ResidentSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $search = $request->string('q')->toString();

        if (mb_strlen($search) < 2) {
            return response()->json([]);
        }

        $residents = Resident::query()
            ->with('familyHead:id,address')
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'nik', 'family_head_id'])
            ->map(fn (Resident $resident) => [
                'id' => $resident->id,
                'name' => $resident->name,
                'nik' => $resident->nik,
                'address' => $resident->familyHead?->address,
            ]);

        return response()->json($residents);
    }
}
