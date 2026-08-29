<?php

namespace App\Services;

use App\Models\Letter;
use App\Models\MasterRw;
use Illuminate\Support\Carbon;

/**
 * Menghasilkan nomor agenda surat berformat "001/RW-001/VIII/2026"
 * (urut/RW-nomor/bulan romawi/tahun), sesuai FR03.3 — nomor urut direset
 * tiap tahun berjalan.
 */
class LetterNumberGenerator
{
    private const ROMAN_MONTHS = [
        1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
        7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
    ];

    public function generate(Carbon $issuedDate): string
    {
        $year = $issuedDate->year;

        $sequence = Letter::query()
            ->whereYear('issued_date', $year)
            ->count() + 1;

        $nomorRw = MasterRw::query()->value('nomor_rw') ?? '001';

        return sprintf(
            '%03d/RW-%s/%s/%d',
            $sequence,
            $nomorRw,
            self::ROMAN_MONTHS[$issuedDate->month],
            $year,
        );
    }
}
