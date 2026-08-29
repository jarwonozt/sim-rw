<?php

namespace App\Exports;

use App\Models\Resident;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Export data Penduduk untuk laporan ke kelurahan (FR02.4). Otomatis
 * dibatasi ke RT milik Ketua RT yang login lewat scope pada model Resident.
 */
class ResidentsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection(): Collection
    {
        return Resident::query()
            ->with('familyHead.rt')
            ->orderBy('family_head_id')
            ->get();
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

    /**
     * @return array<int, mixed>
     */
    public function map($resident): array
    {
        return [
            $resident->familyHead->no_kk,
            $resident->familyHead->rt->nomor_rt,
            $resident->familyHead->address,
            $resident->nik,
            $resident->name,
            $resident->gender === 'L' ? 'Laki-laki' : 'Perempuan',
            $resident->birth_place,
            $resident->birth_date?->format('d-m-Y'),
            $resident->relationship_status,
            $resident->occupation,
            $resident->religion,
            $resident->education,
            $resident->marital_status,
            $resident->phone,
        ];
    }
}
