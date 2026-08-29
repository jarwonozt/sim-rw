<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTreasuryCategoryRequest;
use App\Models\TreasuryCategory;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TreasuryCategoryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Treasury/Categories/Index', [
            'categories' => TreasuryCategory::query()->withCount('treasuries')->orderBy('type')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreTreasuryCategoryRequest $request): RedirectResponse
    {
        TreasuryCategory::create($request->validated());

        return to_route('treasury-categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(StoreTreasuryCategoryRequest $request, TreasuryCategory $treasuryCategory): RedirectResponse
    {
        $treasuryCategory->update($request->validated());

        return to_route('treasury-categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(TreasuryCategory $treasuryCategory): RedirectResponse
    {
        if ($treasuryCategory->treasuries()->exists()) {
            return back()->with('error', 'Kategori tidak bisa dihapus karena sudah dipakai di transaksi kas.');
        }

        $treasuryCategory->delete();

        return to_route('treasury-categories.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
