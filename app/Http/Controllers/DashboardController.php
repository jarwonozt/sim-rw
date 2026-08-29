<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Complaint;
use App\Models\FamilyHead;
use App\Models\Resident;
use App\Models\Treasury;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Kelompok umur piramida penduduk (PRD Bagian 7), rentang 5 tahun
     * dengan kelompok terbuka di ujung atas.
     *
     * @var array<int, string>
     */
    private const AGE_BANDS = [
        '0-4', '5-9', '10-14', '15-19', '20-24', '25-29', '30-34', '35-39',
        '40-44', '45-49', '50-54', '55-59', '60-64', '65-69', '70-74', '75+',
    ];

    private const TREND_MONTHS = 6;

    /**
     * Menampilkan statistik dasar, piramida penduduk, tren kas bulanan,
     * alokasi anggaran, dan aktivitas terbaru (PRD Bagian 7).
     *
     * Query KK/Penduduk/Pengaduan otomatis dibatasi ke wilayah RT milik
     * Ketua RT yang login lewat global scope pada masing-masing model.
     */
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $canViewFinance = in_array($user->role, ['super_admin', 'ketua_rw', 'bendahara'], true);
        $canViewActivity = in_array($user->role, ['super_admin', 'ketua_rw'], true);

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_kk' => FamilyHead::count(),
                'total_penduduk' => Resident::count(),
                'total_saldo_kas' => $canViewFinance
                    ? Treasury::where('type', 'in')->sum('amount') - Treasury::where('type', 'out')->sum('amount')
                    : null,
                'total_pengaduan_pending' => Complaint::whereNotIn('status', ['selesai'])->count(),
            ],
            'populationPyramid' => $this->populationPyramid(),
            'monthlyTrend' => $canViewFinance ? $this->monthlyTreasuryTrend() : null,
            'budgetAllocation' => $canViewFinance ? $this->budgetAllocation() : null,
            'recentActivity' => $canViewActivity ? $this->recentActivity() : null,
        ]);
    }

    /**
     * @return array<int, array{age_band: string, male: int, female: int}>
     */
    private function populationPyramid(): array
    {
        $buckets = [];
        foreach (self::AGE_BANDS as $band) {
            $buckets[$band] = ['male' => 0, 'female' => 0];
        }

        $today = Carbon::today();

        foreach (Resident::query()->whereNotNull('birth_date')->get(['gender', 'birth_date']) as $resident) {
            $age = $resident->birth_date->diffInYears($today);
            $band = self::AGE_BANDS[min(intdiv($age, 5), count(self::AGE_BANDS) - 1)];
            $key = $resident->gender === 'L' ? 'male' : 'female';
            $buckets[$band][$key]++;
        }

        return collect($buckets)
            ->map(fn ($counts, $band) => ['age_band' => $band, ...$counts])
            ->values()
            ->all();
    }

    /**
     * Tren kas masuk/keluar N bulan terakhir untuk grafik batang.
     *
     * @return array<int, array{month: string, in: float, out: float}>
     */
    private function monthlyTreasuryTrend(): array
    {
        $start = Carbon::today()->startOfMonth()->subMonths(self::TREND_MONTHS - 1);

        $buckets = [];
        for ($i = 0; $i < self::TREND_MONTHS; $i++) {
            $month = $start->copy()->addMonths($i);
            $buckets[$month->format('Y-m')] = [
                'month' => $month->translatedFormat('M Y'),
                'in' => 0.0,
                'out' => 0.0,
            ];
        }

        Treasury::query()
            ->where('transaction_date', '>=', $start->toDateString())
            ->get(['transaction_date', 'type', 'amount'])
            ->each(function (Treasury $treasury) use (&$buckets) {
                $key = Carbon::parse($treasury->transaction_date)->format('Y-m');

                if (isset($buckets[$key])) {
                    $buckets[$key][$treasury->type] += (float) $treasury->amount;
                }
            });

        return array_values($buckets);
    }

    /**
     * Alokasi anggaran (kas keluar per kategori) bulan berjalan, untuk pie chart.
     *
     * @return array<int, array{name: string, total: float}>
     */
    private function budgetAllocation(): array
    {
        return Treasury::query()
            ->join('treasury_categories', 'treasury_categories.id', '=', 'treasuries.treasury_category_id')
            ->where('treasuries.type', 'out')
            ->whereYear('transaction_date', now()->year)
            ->whereMonth('transaction_date', now()->month)
            ->groupBy('treasury_categories.id', 'treasury_categories.name')
            ->selectRaw('treasury_categories.name as name, SUM(treasuries.amount) as total')
            ->get()
            ->map(fn ($row) => ['name' => $row->name, 'total' => (float) $row->total])
            ->values()
            ->all();
    }

    /**
     * @return array<int, ActivityLog>
     */
    private function recentActivity(): array
    {
        return ActivityLog::query()
            ->with('user:id,name')
            ->latest('created_at')
            ->limit(8)
            ->get()
            ->all();
    }
}
