<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTreasuryRequest;
use App\Http\Requests\UpdateTreasuryRequest;
use App\Models\Treasury;
use App\Models\TreasuryCategory;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class TreasuryController extends Controller
{
    public function index(Request $request): Response
    {
        $month = $request->integer('month') ?: now()->month;
        $year = $request->integer('year') ?: now()->year;
        $type = $request->string('type')->toString();

        $treasuries = Treasury::query()
            ->with('category:id,name,type', 'recordedBy:id,name')
            ->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month)
            ->when($type, fn ($query) => $query->where('type', $type))
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Treasury/Index', [
            'treasuries' => $treasuries,
            'filters' => ['month' => $month, 'year' => $year, 'type' => $type],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Treasury/Create', [
            'categories' => TreasuryCategory::query()->orderBy('type')->orderBy('name')->get(['id', 'name', 'type']),
        ]);
    }

    public function store(StoreTreasuryRequest $request): RedirectResponse
    {
        $category = TreasuryCategory::query()->findOrFail($request->integer('treasury_category_id'));

        $treasury = Treasury::create([
            ...$request->validated(),
            'type' => $category->type,
            'proof_photo' => $request->file('proof_photo')->store('treasuries', 'public'),
            'created_by' => $request->user()->id,
        ]);

        $verb = $treasury->type === 'in' ? 'Mencatat kas masuk' : 'Mencatat kas keluar';
        ActivityLogger::log('treasury.created', "{$verb} sebesar Rp".number_format((float) $treasury->amount, 0, ',', '.').'.');

        return to_route('treasuries.index')->with('success', 'Transaksi kas berhasil dicatat.');
    }

    public function edit(Treasury $treasury): Response
    {
        return Inertia::render('Treasury/Edit', [
            'treasury' => $treasury,
            'categories' => TreasuryCategory::query()->orderBy('type')->orderBy('name')->get(['id', 'name', 'type']),
        ]);
    }

    public function update(UpdateTreasuryRequest $request, Treasury $treasury): RedirectResponse
    {
        $category = TreasuryCategory::query()->findOrFail($request->integer('treasury_category_id'));

        $data = [
            ...$request->validated(),
            'type' => $category->type,
        ];

        if ($request->hasFile('proof_photo')) {
            Storage::disk('public')->delete($treasury->proof_photo);
            $data['proof_photo'] = $request->file('proof_photo')->store('treasuries', 'public');
        }

        $treasury->update($data);

        return to_route('treasuries.index')->with('success', 'Transaksi kas berhasil diperbarui.');
    }

    public function destroy(Treasury $treasury): RedirectResponse
    {
        Storage::disk('public')->delete($treasury->proof_photo);
        $treasury->delete();

        return to_route('treasuries.index')->with('success', 'Transaksi kas berhasil dihapus.');
    }
}
