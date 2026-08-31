<?php

use App\Http\Controllers\Api\V1\AnnouncementController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ComplaintController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\FamilyHeadController;
use App\Http\Controllers\Api\V1\InventoryCategoryController;
use App\Http\Controllers\Api\V1\InventoryItemController;
use App\Http\Controllers\Api\V1\InventoryLoanController;
use App\Http\Controllers\Api\V1\InventoryReportController;
use App\Http\Controllers\Api\V1\LetterController;
use App\Http\Controllers\Api\V1\LetterTemplateController;
use App\Http\Controllers\Api\V1\MasterRtController;
use App\Http\Controllers\Api\V1\MasterRwController;
use App\Http\Controllers\Api\V1\ResidentController;
use App\Http\Controllers\Api\V1\ResidentImportController;
use App\Http\Controllers\Api\V1\ResidentProfileController;
use App\Http\Controllers\Api\V1\TreasuryCategoryController;
use App\Http\Controllers\Api\V1\TreasuryController;
use App\Http\Controllers\Api\V1\TreasuryReportController;
use App\Http\Controllers\Api\V1\WhatsappBroadcastController;
use App\Http\Controllers\Api\V1\WhatsappTemplateController;
use App\Http\Controllers\ResidentImportExportController;
use App\Http\Controllers\ResidentSearchController;
use App\Http\Controllers\TreasuryReportController as WebTreasuryReportController;
use App\Http\Controllers\WilayahController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| REST API v1 (docs/issues/002-rest-api.md)
|--------------------------------------------------------------------------
|
| Setiap endpoint memakai token barrier (Sanctum) — tidak ada rute publik
| tanpa token, kecuali /login itu sendiri (di situlah token diterbitkan).
| RBAC per peran mengikuti persis routes/web.php.
*/

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:login')
        ->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/user', [AuthController::class, 'me'])->name('user');

        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        // "Data Saya" — warga melihat & memperbarui data kependudukannya sendiri.
        Route::get('/data-saya', [ResidentProfileController::class, 'show'])->name('resident-profile.show');
        Route::put('/data-saya', [ResidentProfileController::class, 'update'])->name('resident-profile.update');

        // Master Data: Wilayah & Profil RW — hanya Super Admin & Ketua RW.
        Route::middleware('role:super_admin,ketua_rw')->group(function () {
            Route::get('/wilayah/provinces', [WilayahController::class, 'provinces'])->name('wilayah.provinces');
            Route::get('/wilayah/districts', [WilayahController::class, 'districts'])->name('wilayah.districts');
            Route::get('/wilayah/subdistricts', [WilayahController::class, 'subdistricts'])->name('wilayah.subdistricts');
            Route::get('/wilayah/villages', [WilayahController::class, 'villages'])->name('wilayah.villages');

            Route::get('/rw', [MasterRwController::class, 'show'])->name('rw.show');
            Route::put('/rw', [MasterRwController::class, 'update'])->name('rw.update');

            Route::apiResource('rt', MasterRtController::class)->only(['index', 'store', 'update', 'destroy']);
        });

        // Master Data: Kepala Keluarga & Penduduk — Ketua RT ikut diberi akses,
        // isolasi wilayahnya ditegakkan oleh RtOwnedScope pada Model.
        Route::middleware('role:super_admin,ketua_rw,sekretaris,ketua_rt')->group(function () {
            Route::apiResource('family-heads', FamilyHeadController::class)
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

            Route::get('/residents/search', ResidentSearchController::class)->name('residents.search');
        });

        // Import/Export Excel data Penduduk.
        Route::middleware('role:super_admin,ketua_rw,sekretaris')->group(function () {
            Route::get('/residents-export', [ResidentImportExportController::class, 'export'])->name('residents.export');
            Route::get('/residents-import-template', [ResidentImportExportController::class, 'template'])->name('residents.import-template');
            Route::post('/residents-import', ResidentImportController::class)->name('residents.import');
        });

        // Surat Menyurat — Bendahara sengaja tidak diberi akses (FR01.3).
        Route::middleware('role:super_admin,ketua_rw,sekretaris')->group(function () {
            Route::apiResource('letter-templates', LetterTemplateController::class)
                ->parameters(['letter-templates' => 'letterTemplate'])
                ->only(['index', 'store', 'update', 'destroy']);

            Route::apiResource('letters', LetterController::class)->only(['index', 'store', 'show']);
            Route::get('/letters/{letter}/download', [LetterController::class, 'download'])->name('letters.download');
        });

        // Keuangan / Kas RW — Sekretaris & Ketua RT sengaja tidak diberi akses.
        Route::middleware('role:super_admin,ketua_rw,bendahara')->group(function () {
            Route::apiResource('treasury-categories', TreasuryCategoryController::class)
                ->parameters(['treasury-categories' => 'treasuryCategory'])
                ->only(['index', 'store', 'update', 'destroy']);

            Route::apiResource('treasuries', TreasuryController::class)->except(['show']);

            Route::get('/treasury-report', [TreasuryReportController::class, 'index'])->name('treasury-report.index');
            Route::get('/treasury-report/export-excel', [WebTreasuryReportController::class, 'exportExcel'])->name('treasury-report.export-excel');
            Route::get('/treasury-report/export-pdf', [WebTreasuryReportController::class, 'exportPdf'])->name('treasury-report.export-pdf');
        });

        // Pengaduan & Aspirasi Warga.
        Route::middleware('role:super_admin,ketua_rw,ketua_rt,warga')->group(function () {
            Route::apiResource('complaints', ComplaintController::class)->only(['index', 'store', 'show']);
            Route::patch('/complaints/{complaint}/status', [ComplaintController::class, 'updateStatus'])->name('complaints.update-status');
        });

        // Pengumuman — daftar dibuka untuk warga (otomatis difilter ke yang
        // sudah tayang di controller), kelola penuh hanya Super Admin/Ketua RW.
        Route::middleware('role:super_admin,ketua_rw,warga')
            ->get('/announcements', [AnnouncementController::class, 'index'])
            ->name('announcements.index');

        Route::middleware('role:super_admin,ketua_rw')->group(function () {
            Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
            Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
            Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
        });

        // Inventaris: kategori hanya Sekretaris/Ketua RW (docs/issues/001-modul-inventaris.md#4).
        Route::middleware('role:super_admin,ketua_rw,sekretaris')->group(function () {
            Route::apiResource('inventory-categories', InventoryCategoryController::class)
                ->parameters(['inventory-categories' => 'inventoryCategory'])
                ->only(['index', 'store', 'update', 'destroy']);
        });

        // Inventaris: Barang & Peminjaman — Ketua RT ikut diberi akses untuk
        // mengelola barang/peminjaman miliknya sendiri (RtOwnedScope).
        Route::middleware('role:super_admin,ketua_rw,sekretaris,ketua_rt')->group(function () {
            Route::apiResource('inventory-items', InventoryItemController::class)
                ->parameters(['inventory-items' => 'inventoryItem'])
                ->except(['show']);

            Route::apiResource('inventory-loans', InventoryLoanController::class)
                ->parameters(['inventory-loans' => 'inventoryLoan'])
                ->only(['index', 'store', 'show']);
            Route::patch('/inventory-loans/{inventoryLoan}/return', [InventoryLoanController::class, 'returnItem'])->name('inventory-loans.return');

            Route::get('/inventory-report', [InventoryReportController::class, 'index'])->name('inventory-report.index');
        });

        // WhatsApp: Broadcast & Template — hanya Super Admin & Ketua RW.
        Route::middleware('role:super_admin,ketua_rw')->group(function () {
            Route::get('/whatsapp-broadcast', [WhatsappBroadcastController::class, 'index'])->name('whatsapp-broadcast.index');
            Route::post('/whatsapp-broadcast', [WhatsappBroadcastController::class, 'store'])->name('whatsapp-broadcast.store');

            Route::apiResource('whatsapp-templates', WhatsappTemplateController::class)
                ->parameters(['whatsapp-templates' => 'whatsappTemplate'])
                ->only(['index', 'store', 'update', 'destroy']);
        });
    });
});
