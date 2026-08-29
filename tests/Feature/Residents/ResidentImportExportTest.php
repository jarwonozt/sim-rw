<?php

namespace Tests\Feature\Residents;

use App\Exports\ResidentsTemplateExport;
use App\Models\MasterRt;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ResidentImportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_bendahara_cannot_access_resident_export(): void
    {
        $bendahara = User::factory()->role('bendahara')->create();

        $this->actingAs($bendahara)->get(route('residents.export'))->assertForbidden();
    }

    public function test_sekretaris_can_export_residents(): void
    {
        $sekretaris = User::factory()->role('sekretaris')->create();
        Resident::factory()->create();

        $response = $this->actingAs($sekretaris)->get(route('residents.export'));

        $response->assertOk();
    }

    public function test_sekretaris_can_download_the_import_template(): void
    {
        $sekretaris = User::factory()->role('sekretaris')->create();

        $response = $this->actingAs($sekretaris)->get(route('residents.import-template'));

        $response->assertOk();
    }

    public function test_importing_a_valid_file_creates_family_head_and_resident(): void
    {
        Storage::fake('local');

        $sekretaris = User::factory()->role('sekretaris')->create();
        $rt = MasterRt::factory()->create(['nomor_rt' => '001']);

        $file = $this->makeImportFile([[
            '3171011234560001', $rt->nomor_rt, 'Jl. Contoh No. 1', '3171012345670001', 'Budi Santoso',
            'Laki-laki', 'Jakarta', '17-08-1990', 'Kepala Keluarga', 'Karyawan',
            'Islam', 'S1', 'Kawin', '081234567890',
        ]]);

        $response = $this->actingAs($sekretaris)->post(route('residents.import'), ['file' => $file]);

        $response->assertRedirect();
        $this->assertDatabaseHas('family_heads', ['no_kk' => '3171011234560001', 'rt_id' => $rt->id]);
        $this->assertDatabaseHas('residents', ['nik' => '3171012345670001', 'name' => 'Budi Santoso', 'gender' => 'L']);
    }

    public function test_reimporting_the_same_nik_updates_the_existing_resident(): void
    {
        Storage::fake('local');

        $sekretaris = User::factory()->role('sekretaris')->create();
        $rt = MasterRt::factory()->create(['nomor_rt' => '001']);

        $file = $this->makeImportFile([[
            '3171011234560002', $rt->nomor_rt, 'Jl. Lama', '3171012345670002', 'Nama Lama',
            'Perempuan', '', '', '', '', '', '', '', '',
        ]]);
        $this->actingAs($sekretaris)->post(route('residents.import'), ['file' => $file]);

        $file2 = $this->makeImportFile([[
            '3171011234560002', $rt->nomor_rt, 'Jl. Baru', '3171012345670002', 'Nama Baru',
            'Perempuan', '', '', '', '', '', '', '', '',
        ]]);
        $this->actingAs($sekretaris)->post(route('residents.import'), ['file' => $file2]);

        $this->assertDatabaseCount('residents', 1);
        $this->assertDatabaseHas('residents', ['nik' => '3171012345670002', 'name' => 'Nama Baru']);
    }

    public function test_row_with_unknown_rt_is_skipped_and_reported(): void
    {
        Storage::fake('local');

        $sekretaris = User::factory()->role('sekretaris')->create();

        $file = $this->makeImportFile([[
            '3171011234560003', '999', 'Jl. Tidak Ada RT', '3171012345670003', 'Nama Uji',
            'Laki-laki', '', '', '', '', '', '', '', '',
        ]]);

        $response = $this->actingAs($sekretaris)->post(route('residents.import'), ['file' => $file]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('residents', ['nik' => '3171012345670003']);
    }

    /**
     * @param  array<int, array<int, string>>  $rows
     */
    private function makeImportFile(array $rows): UploadedFile
    {
        $export = new class($rows) implements FromArray, WithHeadings
        {
            public function __construct(private readonly array $rows) {}

            public function array(): array
            {
                return $this->rows;
            }

            public function headings(): array
            {
                return (new ResidentsTemplateExport)->headings();
            }
        };

        $path = 'test-fixtures/'.uniqid('import-').'.xlsx';
        Excel::store($export, $path, 'local');
        $fullPath = Storage::disk('local')->path($path);

        return new UploadedFile($fullPath, 'residents.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }
}
