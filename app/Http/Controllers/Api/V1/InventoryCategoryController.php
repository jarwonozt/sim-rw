<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInventoryCategoryRequest;
use App\Http\Resources\InventoryCategoryResource;
use App\Models\InventoryCategory;
use Illuminate\Http\JsonResponse;

class InventoryCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = InventoryCategory::query()->withCount('items')->orderBy('name')->get();

        return response()->json(['data' => InventoryCategoryResource::collection($categories)]);
    }

    public function store(StoreInventoryCategoryRequest $request): JsonResponse
    {
        $category = InventoryCategory::create($request->validated());

        return response()->json(['data' => new InventoryCategoryResource($category)], 201);
    }

    public function update(StoreInventoryCategoryRequest $request, InventoryCategory $inventoryCategory): JsonResponse
    {
        $inventoryCategory->update($request->validated());

        return response()->json(['data' => new InventoryCategoryResource($inventoryCategory)]);
    }

    public function destroy(InventoryCategory $inventoryCategory): JsonResponse
    {
        if ($inventoryCategory->items()->exists()) {
            return response()->json([
                'message' => 'Kategori tidak bisa dihapus karena masih dipakai barang.',
            ], 422);
        }

        $inventoryCategory->delete();

        return response()->json(['message' => 'Kategori berhasil dihapus.']);
    }
}
