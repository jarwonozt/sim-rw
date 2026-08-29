<?php

namespace App\Imports;

use App\Models\FamilyHead;
use App\Models\MasterRt;
use App\Models\Resident;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Import massal data Penduduk dari Excel (FR02.4), untuk migrasi data lama.
 * Kolom mengikuti format yang dihasilkan ResidentsExport agar bisa
 * diekspor-edit-impor ulang. Setiap baris divalidasi independen; baris yang
 * gagal dilewati dan dicatat di $errors tanpa membatalkan baris lainnya.
 */
class ResidentsImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;

    public int $skipped = 0;

    /** @var array<int, string> */
    public array $errors = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +1 untuk heading row, +1 untuk index 0-based.

            $data = $this->normalize($row);
            $validator = Validator::make($data, [
                'no_kk' => ['required', 'digits:16'],
                'rt' => ['required', 'string'],
                'alamat' => ['required', 'string'],
                'nik' => ['required', 'digits:16'],
                'nama' => ['required', 'string'],
                'jenis_kelamin' => ['required', 'in:L,P'],
            ]);

            if ($validator->fails()) {
                $this->skipped++;
                $this->errors[] = "Baris {$rowNumber}: ".$validator->errors()->first();

                continue;
            }

            $rt = MasterRt::query()->where('nomor_rt', $data['rt'])->first();

            if (! $rt) {
                $this->skipped++;
                $this->errors[] = "Baris {$rowNumber}: RT \"{$data['rt']}\" tidak ditemukan.";

                continue;
            }

            $familyHead = FamilyHead::query()->updateOrCreate(
                ['no_kk' => $data['no_kk']],
                ['rt_id' => $rt->id, 'address' => $data['alamat']],
            );

            Resident::query()->updateOrCreate(
                ['nik' => $data['nik']],
                [
                    'family_head_id' => $familyHead->id,
                    'name' => $data['nama'],
                    'gender' => $data['jenis_kelamin'],
                    'birth_place' => $data['tempat_lahir'] ?: null,
                    'birth_date' => $data['tanggal_lahir'],
                    'relationship_status' => $data['hubungan_dalam_keluarga'] ?: null,
                    'occupation' => $data['pekerjaan'] ?: null,
                    'religion' => $data['agama'] ?: null,
                    'education' => $data['pendidikan'] ?: null,
                    'marital_status' => $data['status_perkawinan'] ?: null,
                    'phone' => $data['no_hp'] ?: null,
                ],
            );

            $this->imported++;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function normalize(Collection $row): array
    {
        $genderRaw = trim((string) $row->get('jenis_kelamin'));
        $gender = match (mb_strtoupper($genderRaw)) {
            'L', 'LAKI-LAKI', 'LAKI LAKI' => 'L',
            'P', 'PEREMPUAN' => 'P',
            default => $genderRaw,
        };

        return [
            'no_kk' => trim((string) $row->get('no_kk')),
            'rt' => trim((string) $row->get('rt')),
            'alamat' => trim((string) $row->get('alamat')),
            'nik' => trim((string) $row->get('nik')),
            'nama' => trim((string) $row->get('nama')),
            'jenis_kelamin' => $gender,
            'tempat_lahir' => trim((string) $row->get('tempat_lahir')),
            'tanggal_lahir' => $this->parseDate($row->get('tanggal_lahir')),
            'hubungan_dalam_keluarga' => trim((string) $row->get('hubungan_dalam_keluarga')),
            'pekerjaan' => trim((string) $row->get('pekerjaan')),
            'agama' => trim((string) $row->get('agama')),
            'pendidikan' => trim((string) $row->get('pendidikan')),
            'status_perkawinan' => trim((string) $row->get('status_perkawinan')),
            'no_hp' => trim((string) $row->get('no_hp')),
        ];
    }

    private function parseDate(mixed $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->toDateString();
        }

        try {
            return Carbon::parse((string) $value)->toDateString();
        } catch (\Exception) {
            return null;
        }
    }
}
