<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Complaint;
use App\Models\FamilyHead;
use App\Models\LetterTemplate;
use App\Models\MasterRt;
use App\Models\MasterRw;
use App\Models\Resident;
use App\Models\Treasury;
use App\Models\TreasuryCategory;
use App\Models\User;
use App\Models\Village;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Data contoh untuk pengembangan lokal: satu RW dengan dua RT, akun demo
 * untuk setiap peran (lihat PRD Bagian 1.3), beberapa KK/Penduduk, template
 * surat, dan kategori kas.
 */
class DemoSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $village = Village::query()->inRandomOrder()->first();

        if (! $village) {
            $this->command?->warn('Tabel villages kosong — jalankan WilayahSeeder terlebih dahulu.');

            return;
        }

        $superAdmin = User::factory()->role('super_admin')->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@sim-rw.test',
        ]);

        $ketuaRw = User::factory()->role('ketua_rw')->create([
            'name' => 'Ketua RW',
            'email' => 'ketuarw@sim-rw.test',
        ]);

        $sekretaris = User::factory()->role('sekretaris')->create([
            'name' => 'Sekretaris RW',
            'email' => 'sekretaris@sim-rw.test',
        ]);

        $bendahara = User::factory()->role('bendahara')->create([
            'name' => 'Bendahara RW',
            'email' => 'bendahara@sim-rw.test',
        ]);

        $rw = MasterRw::factory()->create([
            'village_id' => $village->id,
            'nomor_rw' => '001',
            'ketua_rw_id' => $ketuaRw->id,
        ]);

        $ketuaRt1 = User::factory()->role('ketua_rt')->create([
            'name' => 'Ketua RT 001',
            'email' => 'ketuart1@sim-rw.test',
        ]);

        $ketuaRt2 = User::factory()->role('ketua_rt')->create([
            'name' => 'Ketua RT 002',
            'email' => 'ketuart2@sim-rw.test',
        ]);

        $rt1 = MasterRt::factory()->create([
            'master_rw_id' => $rw->id,
            'nomor_rt' => '001',
            'ketua_rt_id' => $ketuaRt1->id,
        ]);

        $rt2 = MasterRt::factory()->create([
            'master_rw_id' => $rw->id,
            'nomor_rt' => '002',
            'ketua_rt_id' => $ketuaRt2->id,
        ]);

        foreach ([$rt1, $rt2] as $rt) {
            FamilyHead::factory(5)
                ->create(['rt_id' => $rt->id])
                ->each(function (FamilyHead $familyHead) {
                    $head = Resident::factory()->familyHeadRole()->create([
                        'family_head_id' => $familyHead->id,
                    ]);

                    Resident::factory(random_int(1, 3))->create([
                        'family_head_id' => $familyHead->id,
                    ]);

                    if (random_int(0, 1) === 1) {
                        User::factory()->role('warga')->create([
                            'name' => $head->name,
                            'email' => 'warga'.$head->id.'@sim-rw.test',
                            'resident_id' => $head->id,
                        ]);
                    }
                });
        }

        $demoFamilyHead = FamilyHead::factory()->create(['rt_id' => $rt1->id]);
        $demoResident = Resident::factory()->familyHeadRole()->create([
            'family_head_id' => $demoFamilyHead->id,
            'name' => 'Warga Demo',
        ]);
        $demoWarga = User::factory()->role('warga')->create([
            'name' => 'Warga Demo',
            'email' => 'warga@sim-rw.test',
            'resident_id' => $demoResident->id,
        ]);

        LetterTemplate::factory()->create([
            'name' => 'Surat Keterangan Domisili',
            'type' => 'domisili',
        ]);

        LetterTemplate::factory()->create([
            'name' => 'Surat Keterangan Tidak Mampu (SKTM)',
            'type' => 'sktm',
        ]);

        LetterTemplate::factory()->create([
            'name' => 'Surat Keterangan Usaha',
            'type' => 'usaha',
        ]);

        $incomeCategories = collect(['Iuran Warga', 'Sumbangan'])
            ->map(fn ($name) => TreasuryCategory::factory()->create(['name' => $name, 'type' => 'in']));

        $expenseCategories = collect(['Listrik & Kebersihan', 'Konsumsi Kegiatan'])
            ->map(fn ($name) => TreasuryCategory::factory()->create(['name' => $name, 'type' => 'out']));

        foreach (range(0, 2) as $monthsAgo) {
            $date = now()->subMonths($monthsAgo);

            $incomeCategories->each(function (TreasuryCategory $category) use ($date, $bendahara) {
                Treasury::factory()->create([
                    'treasury_category_id' => $category->id,
                    'type' => 'in',
                    'transaction_date' => $date->copy()->startOfMonth()->addDays(4),
                    'created_by' => $bendahara->id,
                ]);
            });

            $expenseCategories->each(function (TreasuryCategory $category) use ($date, $bendahara) {
                Treasury::factory()->create([
                    'treasury_category_id' => $category->id,
                    'type' => 'out',
                    'transaction_date' => $date->copy()->startOfMonth()->addDays(14),
                    'created_by' => $bendahara->id,
                ]);
            });
        }

        $complaint = Complaint::factory()->create([
            'user_id' => $demoWarga->id,
            'rt_id' => $rt1->id,
            'title' => 'Lampu jalan gang mati',
            'description' => 'Lampu penerangan di gang RT 001 sudah mati sejak 3 hari lalu, mohon segera diperbaiki.',
            'status' => 'menunggu_verifikasi_rt',
        ]);
        $complaint->logs()->create([
            'status' => 'menunggu_verifikasi_rt',
            'changed_by' => $demoWarga->id,
        ]);

        Announcement::factory()->create([
            'title' => 'Kerja Bakti Akbar Bulanan',
            'content' => "Warga RW 001 dimohon berpartisipasi dalam kerja bakti akbar yang akan dilaksanakan pada hari Minggu pukul 07.00 WIB di halaman balai warga.\n\nMohon membawa alat kebersihan masing-masing.",
            'publish_date' => now()->subDays(2),
            'expire_date' => now()->addWeeks(2),
            'created_by' => $ketuaRw->id,
        ]);

        Announcement::factory()->create([
            'title' => 'Jadwal Pembayaran Iuran Bulanan',
            'content' => 'Pembayaran iuran warga bulan ini dapat dilakukan melalui Bendahara RW paling lambat tanggal 10.',
            'publish_date' => now()->subWeek(),
            'expire_date' => null,
            'created_by' => $ketuaRw->id,
        ]);

        $this->command?->info("Demo siap. Login sebagai: {$superAdmin->email}, {$ketuaRw->email}, {$sekretaris->email}, {$demoWarga->email}, dst. Password: password");
    }
}
