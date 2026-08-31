<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ApiGuideController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FamilyHeadController;
use App\Http\Controllers\InventoryCategoryController;
use App\Http\Controllers\InventoryItemController;
use App\Http\Controllers\InventoryLoanController;
use App\Http\Controllers\InventoryReportController;
use App\Http\Controllers\LetterController;
use App\Http\Controllers\LetterTemplateController;
use App\Http\Controllers\MasterRtController;
use App\Http\Controllers\MasterRwController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicAnnouncementController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\ResidentImportExportController;
use App\Http\Controllers\ResidentProfileController;
use App\Http\Controllers\ResidentSearchController;
use App\Http\Controllers\TreasuryCategoryController;
use App\Http\Controllers\TreasuryController;
use App\Http\Controllers\TreasuryReportController;
use App\Http\Controllers\WhatsappBroadcastController;
use App\Http\Controllers\WhatsappTemplateController;
use App\Http\Controllers\WilayahController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
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

// Pencarian cepat penduduk (combobox Surat & Peminjaman Inventaris).
Route::middleware(['auth', 'role:super_admin,ketua_rw,sekretaris,ketua_rt'])
    ->get('/residents/search', ResidentSearchController::class)->name('residents.search');

// Modul Surat Menyurat (FR03) — Bendahara sengaja tidak diberi akses (FR01.3).
Route::middleware(['auth', 'role:super_admin,ketua_rw,sekretaris'])->group(function () {
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

// "Data Saya" — Warga melihat & memperbarui data kependudukannya sendiri
// (kontak/non-identitas saja, lihat UpdateOwnResidentRequest).
Route::middleware(['auth', 'role:warga'])->group(function () {
    Route::get('/data-saya', [ResidentProfileController::class, 'edit'])->name('resident-profile.edit');
    Route::put('/data-saya', [ResidentProfileController::class, 'update'])->name('resident-profile.update');
});

// Modul Inventaris (FR08) — kategori hanya dikelola Sekretaris/Ketua RW
// (docs/issues/001-modul-inventaris.md#4); Bendahara sengaja tidak diberi akses.
Route::middleware(['auth', 'role:super_admin,ketua_rw,sekretaris'])->group(function () {
    Route::resource('inventory-categories', InventoryCategoryController::class)
        ->parameters(['inventory-categories' => 'inventoryCategory'])
        ->only(['index', 'store', 'update', 'destroy']);
});

// Modul Inventaris: Barang & Peminjaman — Ketua RT ikut diberi akses untuk
// mengelola barang/peminjaman miliknya sendiri (RtOwnedScope, lihat
// app/Models/Scopes/RtOwnedThroughInventoryItemScope.php).
Route::middleware(['auth', 'role:super_admin,ketua_rw,sekretaris,ketua_rt'])->group(function () {
    Route::resource('inventory-items', InventoryItemController::class)
        ->parameters(['inventory-items' => 'inventoryItem'])
        ->except(['show']);

    Route::resource('inventory-loans', InventoryLoanController::class)
        ->parameters(['inventory-loans' => 'inventoryLoan'])
        ->only(['index', 'create', 'store', 'show']);
    Route::patch('/inventory-loans/{inventoryLoan}/return', [InventoryLoanController::class, 'returnItem'])->name('inventory-loans.return');

    Route::get('/inventory-report', [InventoryReportController::class, 'index'])->name('inventory-report.index');
});

// WhatsApp: Broadcast & Template Notifikasi — hanya Super Admin & Ketua RW,
// setara dengan hak menerbitkan Pengumuman.
Route::middleware(['auth', 'role:super_admin,ketua_rw'])->group(function () {
    Route::get('/whatsapp-broadcast', [WhatsappBroadcastController::class, 'index'])->name('whatsapp-broadcast.index');
    Route::post('/whatsapp-broadcast', [WhatsappBroadcastController::class, 'store'])->name('whatsapp-broadcast.store');

    Route::resource('whatsapp-templates', WhatsappTemplateController::class)
        ->parameters(['whatsapp-templates' => 'whatsappTemplate'])
        ->only(['index', 'store', 'update', 'destroy']);
});

// Panduan REST API (docs/issues/002-rest-api.md) — hanya Super Admin, karena
// audiensnya developer aplikasi mobile, bukan pengurus RW/RT sehari-hari.
Route::middleware(['auth', 'role:super_admin'])
    ->get('/panduan-api', ApiGuideController::class)
    ->name('api-guide.index');

require __DIR__.'/auth.php';
