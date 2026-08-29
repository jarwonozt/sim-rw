<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Template kosong (dengan satu baris contoh) untuk impor massal Penduduk
 * (FR02.4), memakai kolom yang sama dengan ResidentsExport.
 */
class ResidentsTemplateExport implements FromArray, WithHeadings
{
    /**
     * @return array<int, array<int, string>>
     */
    public function array(): array
    {
        return [
            [
                '3171011234560001', '001', 'Jl. Contoh No. 1', '3171012345670001', 'Contoh Nama',
                'Laki-laki', 'Jakarta', '17-08-1990', 'Kepala Keluarga', 'Karyawan Swasta',
                'Islam', 'S1', 'Kawin', '081234567890',
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'No KK', 'RT', 'Alamat', 'NIK', 'Nama', 'Jenis Kelamin',
            'Tempat Lahir', 'Tanggal Lahir', 'Hubungan dalam Keluarga',
            'Pekerjaan', 'Agama', 'Pendidikan', 'Status Perkawinan', 'No HP',
        ];
    }
}
