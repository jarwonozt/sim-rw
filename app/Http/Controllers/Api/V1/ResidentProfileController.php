<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOwnResidentRequest;
use App\Http\Resources\ResidentResource;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * "Data Saya" — warga melihat & memperbarui data kependudukan miliknya
 * sendiri lewat API (paritas dengan ResidentProfileController web).
 */
class ResidentProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $resident = $request->user()->resident()->with('familyHead.rt')->first();

        return response()->json(['data' => $resident ? new ResidentResource($resident) : null]);
    }

    public function update(UpdateOwnResidentRequest $request): JsonResponse
    {
        $resident = $request->user()->resident;

        if (! $resident) {
            return response()->json([
                'message' => 'Akun Anda belum terhubung dengan data penduduk.',
            ], 422);
        }

        $resident->update($request->validated());

        ActivityLogger::log('resident.self_updated', "{$request->user()->name} memperbarui data kependudukannya sendiri.");

        return response()->json(['data' => new ResidentResource($resident)]);
    }
}
