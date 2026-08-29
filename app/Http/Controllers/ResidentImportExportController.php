<?php

namespace App\Http\Controllers;

use App\Exports\ResidentsExport;
use App\Exports\ResidentsTemplateExport;
use App\Imports\ResidentsImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ResidentImportExportController extends Controller
{
    public function export(): BinaryFileResponse
    {
        return Excel::download(new ResidentsExport, 'data-penduduk-'.now()->format('Y-m-d').'.xlsx');
    }

    public function template(): BinaryFileResponse
    {
        return Excel::download(new ResidentsTemplateExport, 'template-import-penduduk.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ]);

        $import = new ResidentsImport;
        Excel::import($import, $request->file('file'));

        if ($import->imported > 0) {
            $message = "Berhasil mengimpor {$import->imported} data penduduk.";

            if ($import->skipped > 0) {
                $message .= " {$import->skipped} baris dilewati karena tidak valid.";
            }

            return back()
                ->with('success', $message)
                ->with('importErrors', $import->errors);
        }

        return back()
            ->with('error', 'Tidak ada data yang berhasil diimpor. Periksa format berkas.')
            ->with('importErrors', $import->errors);
    }
}
