<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreResidentRequest;
use App\Http\Requests\UpdateResidentRequest;
use App\Http\Resources\ResidentResource;
use App\Models\FamilyHead;
use App\Models\Resident;
use Illuminate\Http\JsonResponse;

class ResidentController extends Controller
{
    public function store(StoreResidentRequest $request, FamilyHead $familyHead): JsonResponse
    {
        $resident = $familyHead->residents()->create($request->validated());

        return response()->json(['data' => new ResidentResource($resident)], 201);
    }

    public function update(UpdateResidentRequest $request, Resident $resident): JsonResponse
    {
        $resident->update($request->validated());

        return response()->json(['data' => new ResidentResource($resident)]);
    }

    public function destroy(Resident $resident): JsonResponse
    {
        if ($resident->userAccount()->exists()) {
            return response()->json([
                'message' => 'Penduduk tidak bisa dihapus karena memiliki akun pengguna aktif.',
            ], 422);
        }

        $resident->delete();

        return response()->json(['message' => 'Data penduduk berhasil dihapus.']);
    }
}
