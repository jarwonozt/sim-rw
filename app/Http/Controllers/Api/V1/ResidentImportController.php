<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Imports\ResidentsImport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Import Excel data penduduk (FR API.3) — versi JSON dari
 * `ResidentImportExportController::import` (yang mengandalkan session flash,
 * tidak cocok untuk klien API stateless).
 */
class ResidentImportController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ]);

        $import = new ResidentsImport;
        Excel::import($import, $request->file('file'));

        if ($import->imported === 0) {
            return response()->json([
                'message' => 'Tidak ada data yang berhasil diimpor. Periksa format berkas.',
                'imported' => 0,
                'skipped' => $import->skipped,
                'errors' => $import->errors,
            ], 422);
        }

        return response()->json([
            'message' => "Berhasil mengimpor {$import->imported} data penduduk.",
            'imported' => $import->imported,
            'skipped' => $import->skipped,
            'errors' => $import->errors,
        ]);
    }
}
