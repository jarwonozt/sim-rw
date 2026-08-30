<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReturnInventoryLoanRequest;
use App\Http\Requests\StoreInventoryLoanRequest;
use App\Models\InventoryItem;
use App\Models\InventoryLoan;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryLoanController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->string('status')->toString();

        $loans = InventoryLoan::query()
            ->with('item:id,name,code', 'handledBy:id,name')
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByDesc('loan_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $loans->getCollection()->transform(function (InventoryLoan $loan) {
            $loan->is_overdue = $loan->status === 'dipinjam' && $loan->due_date->isPast();

            return $loan;
        });

        return Inertia::render('Inventory/Loans/Index', [
            'loans' => $loans,
            'filters' => ['status' => $status],
        ]);
    }

    public function create(): Response
    {
        $items = InventoryItem::query()
            ->with('category:id,name')
            ->withSum(['loans as borrowed_quantity' => fn ($query) => $query->where('status', 'dipinjam')], 'quantity_borrowed')
            ->orderBy('name')
            ->get()
            ->map(fn (InventoryItem $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->code,
                'category' => $item->category?->name,
                'available_quantity' => $item->quantity - (int) $item->borrowed_quantity,
            ])
            ->filter(fn (array $item) => $item['available_quantity'] > 0)
            ->values();

        return Inertia::render('Inventory/Loans/Create', [
            'items' => $items,
        ]);
    }

    public function store(StoreInventoryLoanRequest $request): RedirectResponse
    {
        $item = InventoryItem::query()->findOrFail($request->integer('inventory_item_id'));
        $borrowed = (int) $item->loans()->where('status', 'dipinjam')->sum('quantity_borrowed');
        $available = $item->quantity - $borrowed;

        if ($request->integer('quantity_borrowed') > $available) {
            return back()->withErrors([
                'quantity_borrowed' => "Stok tersedia hanya {$available} unit.",
            ])->withInput();
        }

        $loan = InventoryLoan::create([
            ...$request->validated(),
            'status' => 'dipinjam',
            'handled_by' => $request->user()->id,
        ]);

        ActivityLogger::log(
            'inventory.loan_created',
            "Mencatat peminjaman \"{$item->name}\" oleh {$loan->borrower_name}."
        );

        return to_route('inventory-loans.index')->with('success', 'Peminjaman berhasil dicatat.');
    }

    public function show(InventoryLoan $inventoryLoan): Response
    {
        $inventoryLoan->load('item.category:id,name', 'resident:id,name', 'handledBy:id,name');
        $inventoryLoan->is_overdue = $inventoryLoan->status === 'dipinjam' && $inventoryLoan->due_date->isPast();

        return Inertia::render('Inventory/Loans/Show', [
            'loan' => $inventoryLoan,
        ]);
    }

    public function returnItem(ReturnInventoryLoanRequest $request, InventoryLoan $inventoryLoan): RedirectResponse
    {
        if ($inventoryLoan->status !== 'dipinjam') {
            return back()->with('error', 'Peminjaman ini sudah selesai dicatat.');
        }

        $returnedCondition = $request->validated('returned_condition');

        $inventoryLoan->update([
            'return_date' => now(),
            'returned_condition' => $returnedCondition,
            'status' => $returnedCondition === 'hilang' ? 'hilang' : 'dikembalikan',
            'notes' => $request->validated('notes'),
        ]);

        if ($returnedCondition === 'hilang') {
            $inventoryLoan->item()->decrement('quantity', $inventoryLoan->quantity_borrowed);
        }

        ActivityLogger::log(
            'inventory.loan_returned',
            "Mencatat pengembalian \"{$inventoryLoan->item->name}\" oleh {$inventoryLoan->borrower_name}."
        );

        return to_route('inventory-loans.index')->with('success', 'Pengembalian berhasil dicatat.');
    }
}
