<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryLoan;
use App\Models\MasterRt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class InventoryReportController extends Controller
{
    public function index(Request $request): Response
    {
        $rtId = $request->integer('rt_id') ?: null;

        $itemsQuery = InventoryItem::query()->when($rtId, fn ($query) => $query->where('rt_id', $rtId));

        $byCondition = (clone $itemsQuery)
            ->select('condition', DB::raw('count(*) as total'))
            ->groupBy('condition')
            ->pluck('total', 'condition');

        $byCategory = (clone $itemsQuery)
            ->join('inventory_categories', 'inventory_categories.id', '=', 'inventory_items.inventory_category_id')
            ->groupBy('inventory_categories.id', 'inventory_categories.name')
            ->select('inventory_categories.name as category', DB::raw('count(*) as total'), DB::raw('sum(inventory_items.quantity) as total_quantity'))
            ->get();

        $loansQuery = InventoryLoan::query()
            ->when($rtId, fn ($query) => $query->whereHas('item', fn ($query) => $query->where('rt_id', $rtId)));

        $activeLoans = (clone $loansQuery)->where('status', 'dipinjam')->count();
        $overdueLoans = (clone $loansQuery)
            ->where('status', 'dipinjam')
            ->where('due_date', '<', now()->toDateString())
            ->count();

        $recentLoans = (clone $loansQuery)
            ->with('item:id,name,code', 'handledBy:id,name')
            ->orderByDesc('loan_date')
            ->limit(10)
            ->get();

        return Inertia::render('Inventory/Report', [
            'summary' => [
                'total_items' => (clone $itemsQuery)->count(),
                'total_quantity' => (int) (clone $itemsQuery)->sum('quantity'),
                'by_condition' => $byCondition,
                'active_loans' => $activeLoans,
                'overdue_loans' => $overdueLoans,
            ],
            'byCategory' => $byCategory,
            'recentLoans' => $recentLoans,
            'rtOptions' => MasterRt::query()->orderBy('nomor_rt')->get(['id', 'nomor_rt']),
            'filters' => ['rt_id' => $rtId],
        ]);
    }
}
