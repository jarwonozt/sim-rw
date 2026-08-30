<?php

namespace App\Services;

use App\Models\InventoryItem;
use Illuminate\Support\Carbon;

/**
 * Menghasilkan kode inventaris berformat "INV-2026-0001" (urut per tahun
 * dibuat), sesuai FR08.2 — nomor urut direset tiap tahun berjalan.
 */
class InventoryCodeGenerator
{
    public function generate(Carbon $date): string
    {
        $year = $date->year;

        $sequence = InventoryItem::withoutGlobalScopes()
            ->whereYear('created_at', $year)
            ->count() + 1;

        return sprintf('INV-%d-%04d', $year, $sequence);
    }
}
