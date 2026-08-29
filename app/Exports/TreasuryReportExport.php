<?php

namespace App\Exports;

use App\Models\Treasury;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TreasuryReportExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        private readonly string $periodStart,
        private readonly string $periodEnd,
    ) {}

    public function collection(): Collection
    {
        return Treasury::query()
            ->with('category:id,name', 'recordedBy:id,name')
            ->whereBetween('transaction_date', [$this->periodStart, $this->periodEnd])
            ->orderBy('transaction_date')
            ->get();
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Tanggal', 'Jenis', 'Kategori', 'Keterangan', 'Jumlah (Rp)', 'Dicatat Oleh'];
    }

    /**
     * @return array<int, mixed>
     */
    public function map($treasury): array
    {
        return [
            $treasury->transaction_date->format('d-m-Y'),
            $treasury->type === 'in' ? 'Masuk' : 'Keluar',
            $treasury->category->name,
            $treasury->description,
            (float) $treasury->amount,
            $treasury->recordedBy->name,
        ];
    }
}
