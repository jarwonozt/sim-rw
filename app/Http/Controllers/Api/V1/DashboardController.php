<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Complaint;
use App\Models\FamilyHead;
use App\Models\Resident;
use App\Models\Treasury;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Ringkasan statistik dashboard (FR API.10) — replika `DashboardController`
 * web, dengan visibilitas per field yang sama persis per peran.
 */
class DashboardController extends Controller
{
    /** @var array<int, string> */
    private const AGE_BANDS = [
        '0-4', '5-9', '10-14', '15-19', '20-24', '25-29', '30-34', '35-39',
        '40-44', '45-49', '50-54', '55-59', '60-64', '65-69', '70-74', '75+',
    ];

    private const TREND_MONTHS = 6;

    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $canViewFinance = in_array($user->role, ['super_admin', 'ketua_rw', 'bendahara'], true);
        $canViewActivity = in_array($user->role, ['super_admin', 'ketua_rw'], true);

        return response()->json([
            'data' => [
                'stats' => [
                    'total_kk' => FamilyHead::count(),
                    'total_penduduk' => Resident::count(),
                    'total_saldo_kas' => $canViewFinance
                        ? Treasury::where('type', 'in')->sum('amount') - Treasury::where('type', 'out')->sum('amount')
                        : null,
                    'total_pengaduan_pending' => Complaint::whereNotIn('status', ['selesai'])->count(),
                ],
                'population_pyramid' => $this->populationPyramid(),
                'monthly_trend' => $canViewFinance ? $this->monthlyTreasuryTrend() : null,
                'budget_allocation' => $canViewFinance ? $this->budgetAllocation() : null,
                'recent_activity' => $canViewActivity ? $this->recentActivity() : null,
            ],
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
