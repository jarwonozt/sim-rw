<?php

namespace App\Http\Controllers;

use App\Exports\TreasuryReportExport;
use App\Models\Treasury;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TreasuryReportController extends Controller
{
    public function index(Request $request): Response
    {
        [$year, $month, $periodStart, $periodEnd] = $this->resolvePeriod($request);

        return Inertia::render('Treasury/Report', [
            'filters' => ['year' => $year, 'month' => $month],
            'summary' => $this->summary($periodStart, $periodEnd),
            'categoryBreakdown' => $this->categoryBreakdown($periodStart, $periodEnd),
        ]);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        [$year, $month, $periodStart, $periodEnd] = $this->resolvePeriod($request);

        $filename = $month
            ? "laporan-kas-{$year}-{$month}.xlsx"
            : "laporan-kas-{$year}.xlsx";

        return Excel::download(new TreasuryReportExport($periodStart, $periodEnd), $filename);
    }

    public function exportPdf(Request $request): HttpResponse
    {
        [$year, $month, $periodStart, $periodEnd] = $this->resolvePeriod($request);

        $transactions = Treasury::query()
            ->with('category:id,name')
            ->whereBetween('transaction_date', [$periodStart, $periodEnd])
            ->orderBy('transaction_date')
            ->get();

        $filename = $month
            ? "laporan-kas-{$year}-{$month}.pdf"
            : "laporan-kas-{$year}.pdf";

        return Pdf::loadView('treasury.report-pdf', [
            'transactions' => $transactions,
            'summary' => $this->summary($periodStart, $periodEnd),
            'periodLabel' => $month
                ? Carbon::parse($periodStart)->translatedFormat('F Y')
                : $year,
        ])->stream($filename);
    }

    /**
     * @return array{0: int, 1: ?int, 2: string, 3: string}
     */
    private function resolvePeriod(Request $request): array
    {
        $year = $request->integer('year') ?: now()->year;
        $month = $request->filled('month') ? $request->integer('month') : null;

        if ($month) {
            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end = $start->copy()->endOfMonth();
        } else {
            $start = Carbon::create($year, 1, 1)->startOfYear();
            $end = $start->copy()->endOfYear();
        }

        return [$year, $month, $start->toDateString(), $end->toDateString()];
    }

    /**
     * @return array{total_masuk: float, total_keluar: float, saldo_periode: float, saldo_akhir: float}
     */
    private function summary(string $periodStart, string $periodEnd): array
    {
        $totalMasuk = (float) Treasury::query()
            ->whereBetween('transaction_date', [$periodStart, $periodEnd])
            ->where('type', 'in')->sum('amount');

        $totalKeluar = (float) Treasury::query()
            ->whereBetween('transaction_date', [$periodStart, $periodEnd])
            ->where('type', 'out')->sum('amount');

        $saldoAkhir = (float) Treasury::query()->where('transaction_date', '<=', $periodEnd)->where('type', 'in')->sum('amount')
            - (float) Treasury::query()->where('transaction_date', '<=', $periodEnd)->where('type', 'out')->sum('amount');

        return [
            'total_masuk' => $totalMasuk,
            'total_keluar' => $totalKeluar,
            'saldo_periode' => $totalMasuk - $totalKeluar,
            'saldo_akhir' => $saldoAkhir,
        ];
    }

    /**
     * @return array{in: array<int, array{name: string, total: float}>, out: array<int, array{name: string, total: float}>}
     */
    private function categoryBreakdown(string $periodStart, string $periodEnd): array
    {
        $rows = Treasury::query()
            ->join('treasury_categories', 'treasury_categories.id', '=', 'treasuries.treasury_category_id')
            ->whereBetween('transaction_date', [$periodStart, $periodEnd])
            ->groupBy('treasury_categories.id', 'treasury_categories.name', 'treasuries.type')
            ->select('treasury_categories.name', 'treasuries.type', DB::raw('SUM(treasuries.amount) as total'))
            ->get();

        return [
            'in' => $rows->where('type', 'in')->map(fn ($row) => ['name' => $row->name, 'total' => (float) $row->total])->values()->all(),
            'out' => $rows->where('type', 'out')->map(fn ($row) => ['name' => $row->name, 'total' => (float) $row->total])->values()->all(),
        ];
    }
}
