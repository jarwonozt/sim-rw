<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FamilyHeadController;
use App\Http\Controllers\LetterController;
use App\Http\Controllers\LetterTemplateController;
use App\Http\Controllers\MasterRtController;
use App\Http\Controllers\MasterRwController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicAnnouncementController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\ResidentImportExportController;
use App\Http\Controllers\ResidentSearchController;
use App\Http\Controllers\TreasuryCategoryController;
use App\Http\Controllers\TreasuryController;
use App\Http\Controllers\TreasuryReportController;
use App\Http\Controllers\WilayahController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Arsip Pengumuman publik (FR06.2) — tidak perlu login.
Route::get('/pengumuman', [PublicAnnouncementController::class, 'index'])->name('public-announcements.index');
Route::get('/pengumuman/{announcement}', [PublicAnnouncementController::class, 'show'])->name('public-announcements.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Master Data: Wilayah, RW, RT (FR02.1) — hanya Super Admin & Ketua RW.
Route::middleware(['auth', 'role:super_admin,ketua_rw'])->group(function () {
    Route::get('/wilayah/provinces', [WilayahController::class, 'provinces'])->name('wilayah.provinces');
    Route::get('/wilayah/districts', [WilayahController::class, 'districts'])->name('wilayah.districts');
    Route::get('/wilayah/subdistricts', [WilayahController::class, 'subdistricts'])->name('wilayah.subdistricts');
    Route::get('/wilayah/villages', [WilayahController::class, 'villages'])->name('wilayah.villages');

    Route::get('/rw', [MasterRwController::class, 'edit'])->name('rw.edit');
    Route::put('/rw', [MasterRwController::class, 'update'])->name('rw.update');

    Route::get('/rt', [MasterRtController::class, 'index'])->name('rt.index');
    Route::post('/rt', [MasterRtController::class, 'store'])->name('rt.store');
    Route::put('/rt/{rt}', [MasterRtController::class, 'update'])->name('rt.update');
    Route::delete('/rt/{rt}', [MasterRtController::class, 'destroy'])->name('rt.destroy');
});

// Master Data: Kepala Keluarga & Penduduk (FR02.2, FR02.3).
// Ketua RT ikut diberi akses; isolasi wilayahnya ditegakkan oleh RtOwnedScope
// pada model FamilyHead/Resident (lihat app/Models/Scopes).
Route::middleware(['auth', 'role:super_admin,ketua_rw,sekretaris,ketua_rt'])->group(function () {
    Route::resource('family-heads', FamilyHeadController::class)
        ->parameters(['family-heads' => 'familyHead'])
        ->except('destroy');
    Route::delete('/family-heads/{familyHead}', [FamilyHeadController::class, 'destroy'])
        ->middleware('role:super_admin,ketua_rw,sekretaris')
        ->name('family-heads.destroy');

    Route::post('/family-heads/{familyHead}/residents', [ResidentController::class, 'store'])->name('residents.store');
    Route::put('/residents/{resident}', [ResidentController::class, 'update'])->name('residents.update');
    Route::delete('/residents/{resident}', [ResidentController::class, 'destroy'])
        ->middleware('role:super_admin,ketua_rw,sekretaris')
        ->name('residents.destroy');
});

// Import/Export Excel data Penduduk (FR02.4) — migrasi data lama & laporan ke kelurahan.
Route::middleware(['auth', 'role:super_admin,ketua_rw,sekretaris'])->group(function () {
    Route::get('/residents-export', [ResidentImportExportController::class, 'export'])->name('residents.export');
    Route::get('/residents-import-template', [ResidentImportExportController::class, 'template'])->name('residents.import-template');
    Route::post('/residents-import', [ResidentImportExportController::class, 'import'])->name('residents.import');
});

// Modul Surat Menyurat (FR03) — Bendahara sengaja tidak diberi akses (FR01.3).
Route::middleware(['auth', 'role:super_admin,ketua_rw,sekretaris'])->group(function () {
    Route::get('/residents/search', ResidentSearchController::class)->name('residents.search');

    Route::resource('letter-templates', LetterTemplateController::class)
        ->parameters(['letter-templates' => 'letterTemplate'])
        ->only(['index', 'store', 'update', 'destroy']);

    Route::resource('letters', LetterController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('/letters/{letter}/download', [LetterController::class, 'download'])->name('letters.download');
});

// Modul Keuangan / Kas RW (FR04) — Sekretaris & Ketua RT sengaja tidak
// diberi akses (Bendahara adalah operator utama, Ketua RW mengawasi).
Route::middleware(['auth', 'role:super_admin,ketua_rw,bendahara'])->group(function () {
    Route::resource('treasury-categories', TreasuryCategoryController::class)
        ->parameters(['treasury-categories' => 'treasuryCategory'])
        ->only(['index', 'store', 'update', 'destroy']);

    Route::resource('treasuries', TreasuryController::class)
        ->except(['show']);

    Route::get('/treasury-report', [TreasuryReportController::class, 'index'])->name('treasury-report.index');
    Route::get('/treasury-report/export-excel', [TreasuryReportController::class, 'exportExcel'])->name('treasury-report.export-excel');
    Route::get('/treasury-report/export-pdf', [TreasuryReportController::class, 'exportPdf'])->name('treasury-report.export-pdf');
});

// Modul Pengaduan & Aspirasi Warga (FR05) — Warga mengajukan, Ketua RT
// memverifikasi, Ketua RW/Super Admin memproses hingga selesai.
Route::middleware(['auth', 'role:super_admin,ketua_rw,ketua_rt,warga'])->group(function () {
    Route::resource('complaints', ComplaintController::class)->only(['index', 'create', 'store', 'show']);
    Route::patch('/complaints/{complaint}/status', [ComplaintController::class, 'updateStatus'])->name('complaints.update-status');
});

// Modul Pengumuman (FR06.1) — hanya Super Admin & Ketua RW yang menerbitkan.
Route::middleware(['auth', 'role:super_admin,ketua_rw'])->group(function () {
    Route::resource('announcements', AnnouncementController::class)->except(['show']);
});

require __DIR__.'/auth.php';
