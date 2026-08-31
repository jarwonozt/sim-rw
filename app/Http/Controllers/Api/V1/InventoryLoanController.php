<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReturnInventoryLoanRequest;
use App\Http\Requests\StoreInventoryLoanRequest;
use App\Http\Resources\InventoryLoanResource;
use App\Models\InventoryItem;
use App\Models\InventoryLoan;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryLoanController extends Controller
{
    public function index(Request $request): JsonResponse
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

        return response()->json(InventoryLoanResource::collection($loans)->response()->getData(true));
    }

    public function store(StoreInventoryLoanRequest $request): JsonResponse
    {
        $item = InventoryItem::query()->findOrFail($request->integer('inventory_item_id'));
        $borrowed = (int) $item->loans()->where('status', 'dipinjam')->sum('quantity_borrowed');
        $available = $item->quantity - $borrowed;

        if ($request->integer('quantity_borrowed') > $available) {
            return response()->json([
                'message' => "Stok tersedia hanya {$available} unit.",
                'errors' => ['quantity_borrowed' => ["Stok tersedia hanya {$available} unit."]],
            ], 422);
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

        return response()->json(['data' => new InventoryLoanResource($loan)], 201);
    }

    public function show(InventoryLoan $inventoryLoan): JsonResponse
    {
        $inventoryLoan->load('item.category:id,name', 'resident:id,name', 'handledBy:id,name');
        $inventoryLoan->is_overdue = $inventoryLoan->status === 'dipinjam' && $inventoryLoan->due_date->isPast();

        return response()->json(['data' => new InventoryLoanResource($inventoryLoan)]);
    }

    public function returnItem(ReturnInventoryLoanRequest $request, InventoryLoan $inventoryLoan): JsonResponse
    {
        if ($inventoryLoan->status !== 'dipinjam') {
            return response()->json(['message' => 'Peminjaman ini sudah selesai dicatat.'], 422);
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

        return response()->json(['data' => new InventoryLoanResource($inventoryLoan)]);
    }
}
