<?php

namespace App\Http\Controllers;

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

    /**
     * Menampilkan 4 kartu statistik dasar dan piramida penduduk (PRD Bagian
     * 7): Total KK, Total Penduduk, Total Saldo Kas, Total Pengaduan
     * Pending.
     *
     * Query KK/Penduduk/Pengaduan otomatis dibatasi ke wilayah RT milik
     * Ketua RT yang login lewat global scope pada masing-masing model.
     */
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $canViewFinance = in_array($user->role, ['super_admin', 'ketua_rw', 'bendahara'], true);

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
}
