<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Mengimpor data wilayah administratif Indonesia (Provinsi, Kabupaten/Kota,
 * Kecamatan, Kelurahan/Desa) dari database/data/*.json ke tabel referensi.
 *
 * Dijalankan lewat query builder (bukan Eloquent) dan di-chunk karena
 * villages.json berisi puluhan ribu baris.
 */
class WilayahSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('provinces')->exists()) {
            $this->command?->info('Data wilayah sudah ada, lewati import.');

            return;
        }

        $this->import('provinces.json', 'provinces', fn (array $row) => [
            'id' => $row['id'],
            'name' => $row['name'],
        ]);

        $this->import('districts.json', 'districts', fn (array $row) => [
            'id' => $row['id'],
            'province_id' => $row['province_id'],
            'name' => $row['name'],
        ]);

        $this->import('subdistricts.json', 'subdistricts', fn (array $row) => [
            'id' => $row['id'],
            'district_id' => $row['district_id'],
            'name' => $row['name'],
        ]);

        $this->import('villages.json', 'villages', fn (array $row) => [
            'id' => $row['id'],
            'subdistrict_id' => $row['subdistrict_id'],
            'name' => $row['name'],
        ]);
    }

    /**
     * @param  \Closure(array<string, mixed>): array<string, mixed>  $mapRow
     */
    private function import(string $fileName, string $table, \Closure $mapRow): void
    {
        $path = database_path('data/'.$fileName);

        if (! File::exists($path)) {
            $this->command?->warn("Berkas {$fileName} tidak ditemukan, lewati.");

            return;
        }

        $rows = json_decode(File::get($path), true);

        collect($rows)
            ->map($mapRow)
            ->chunk(1000)
            ->each(fn ($chunk) => DB::table($table)->insert($chunk->all()));

        $this->command?->info(sprintf('%s: %d baris diimpor ke %s.', $fileName, count($rows), $table));
    }
}
