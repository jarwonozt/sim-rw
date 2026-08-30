<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInventoryCategoryRequest;
use App\Models\InventoryCategory;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class InventoryCategoryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Inventory/Categories/Index', [
            'categories' => InventoryCategory::query()->withCount('items')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreInventoryCategoryRequest $request): RedirectResponse
    {
        InventoryCategory::create($request->validated());

        return to_route('inventory-categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(StoreInventoryCategoryRequest $request, InventoryCategory $inventoryCategory): RedirectResponse
    {
        $inventoryCategory->update($request->validated());

        return to_route('inventory-categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(InventoryCategory $inventoryCategory): RedirectResponse
    {
        if ($inventoryCategory->items()->exists()) {
            return back()->with('error', 'Kategori tidak bisa dihapus karena masih dipakai barang.');
        }

        $inventoryCategory->delete();

        return to_route('inventory-categories.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
