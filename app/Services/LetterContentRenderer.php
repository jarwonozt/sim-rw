<?php

namespace App\Services;

use App\Models\Letter;
use Illuminate\Support\Carbon;

/**
 * Mengganti placeholder dinamis pada isi template surat (FR03.1) dengan data
 * penduduk & surat yang sebenarnya. Placeholder yang didukung didaftarkan di
 * self::PLACEHOLDERS agar bisa ditampilkan sebagai pedoman di form template.
 */
class LetterContentRenderer
{
    public const PLACEHOLDERS = [
        'nama_penduduk', 'nik', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir',
        'pekerjaan', 'alamat', 'rt', 'rw', 'nama_kepala_keluarga',
        'nomor_surat', 'tanggal_surat', 'tujuan',
    ];

    public function render(Letter $letter): string
    {
        $resident = $letter->resident;
        $familyHead = $resident->familyHead;
        $rt = $familyHead?->rt;

        $values = [
            'nama_penduduk' => $resident->name,
            'nik' => $resident->nik,
            'jenis_kelamin' => $resident->gender === 'L' ? 'Laki-laki' : 'Perempuan',
            'tempat_lahir' => $resident->birth_place ?? '-',
            'tanggal_lahir' => $resident->birth_date?->translatedFormat('d F Y') ?? '-',
            'pekerjaan' => $resident->occupation ?? '-',
            'alamat' => $familyHead?->address ?? '-',
            'rt' => $rt?->nomor_rt ?? '-',
            'rw' => $rt?->rw?->nomor_rw ?? '-',
            'nama_kepala_keluarga' => $familyHead?->residents->firstWhere('is_family_head', true)?->name ?? '-',
            'nomor_surat' => $letter->letter_number,
            'tanggal_surat' => Carbon::parse($letter->issued_date)->translatedFormat('d F Y'),
            'tujuan' => $letter->purpose,
        ];

        $content = $letter->template->content;

        foreach ($values as $placeholder => $value) {
            $content = str_replace("[{$placeholder}]", e($value), $content);
        }

        return $content;
    }
}
