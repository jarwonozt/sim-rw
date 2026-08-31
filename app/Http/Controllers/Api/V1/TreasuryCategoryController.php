<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTreasuryCategoryRequest;
use App\Http\Resources\TreasuryCategoryResource;
use App\Models\TreasuryCategory;
use Illuminate\Http\JsonResponse;

class TreasuryCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = TreasuryCategory::query()->withCount('treasuries')->orderBy('type')->orderBy('name')->get();

        return response()->json(['data' => TreasuryCategoryResource::collection($categories)]);
    }

    public function store(StoreTreasuryCategoryRequest $request): JsonResponse
    {
        $category = TreasuryCategory::create($request->validated());

        return response()->json(['data' => new TreasuryCategoryResource($category)], 201);
    }

    public function update(StoreTreasuryCategoryRequest $request, TreasuryCategory $treasuryCategory): JsonResponse
    {
        $treasuryCategory->update($request->validated());

        return response()->json(['data' => new TreasuryCategoryResource($treasuryCategory)]);
    }

    public function destroy(TreasuryCategory $treasuryCategory): JsonResponse
    {
        if ($treasuryCategory->treasuries()->exists()) {
            return response()->json([
                'message' => 'Kategori tidak bisa dihapus karena sudah dipakai di transaksi kas.',
            ], 422);
        }

        $treasuryCategory->delete();

        return response()->json(['message' => 'Kategori berhasil dihapus.']);
    }
}
