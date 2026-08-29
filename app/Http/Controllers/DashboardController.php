<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\FamilyHead;
use App\Models\Resident;
use App\Models\Treasury;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Menampilkan 4 kartu statistik dasar (PRD Bagian 7): Total KK, Total
     * Penduduk, Total Saldo Kas, dan Total Pengaduan Pending.
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
        ]);
    }
}
