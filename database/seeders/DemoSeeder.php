<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Complaint;
use App\Models\FamilyHead;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryLoan;
use App\Models\LetterTemplate;
use App\Models\MasterRt;
use App\Models\MasterRw;
use App\Models\Resident;
use App\Models\Treasury;
use App\Models\TreasuryCategory;
use App\Models\User;
use App\Models\Village;
use App\Models\WhatsappTemplate;
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

        $this->seedWhatsappTemplates();
        $this->seedInventory($sekretaris, $ketuaRw, $rt1, $rt2, $demoResident);

        $this->command?->info("Demo siap. Login sebagai: {$superAdmin->email}, {$ketuaRw->email}, {$sekretaris->email}, {$demoWarga->email}, dst. Password: password");
    }

    /**
     * Template pesan WhatsApp yang umum dipakai pengurus RW sehari-hari —
     * satu di antaranya (event_key "complaint_resolved") otomatis dipasang
     * ke notifikasi sistem, sisanya siap dipakai lewat menu Broadcast.
     */
    private function seedWhatsappTemplates(): void
    {
        $templates = [
            [
                'name' => 'Notifikasi Pengaduan Selesai',
                'event_key' => 'complaint_resolved',
                'content' => "Halo [nama_warga] 👋\n\nPengaduan Anda \"[judul_pengaduan]\" telah *selesai* ditindaklanjuti oleh pengurus RW.\n\nTerima kasih atas partisipasi Anda menjaga lingkungan RW. 🙏",
            ],
            [
                'name' => 'Pengingat Iuran Bulanan',
                'event_key' => null,
                'content' => "Assalamu'alaikum Bapak/Ibu [nama_warga] 🙏\n\nMengingatkan pembayaran iuran warga RT [nomor_rt]/RW [nomor_rw] bulan ini. Pembayaran dapat dilakukan melalui Bendahara RW paling lambat tanggal 10.\n\nTerima kasih atas kerja samanya. 🏡",
            ],
            [
                'name' => 'Undangan Kerja Bakti',
                'event_key' => null,
                'content' => "Assalamu'alaikum warga RT [nomor_rt]/RW [nomor_rw] 🙏\n\nDimohon partisipasinya untuk kerja bakti bersama pada:\n📅 Minggu, pukul 07.00 WIB\n📍 Halaman balai warga\n\nMohon membawa alat kebersihan masing-masing. Terima kasih! 💪",
            ],
            [
                'name' => 'Undangan Rapat RW',
                'event_key' => null,
                'content' => "Assalamu'alaikum Bapak/Ibu [nama_warga] 🙏\n\nDimohon kehadirannya dalam rapat warga RT [nomor_rt]/RW [nomor_rw] untuk membahas agenda penting lingkungan.\n\nMohon hadir tepat waktu. Terima kasih 🙏",
            ],
            [
                'name' => 'Jadwal Ronda/Siskamling',
                'event_key' => null,
                'content' => "Halo [nama_warga] 👮\n\nMengingatkan jadwal ronda malam Anda di RT [nomor_rt] besok malam. Mohon hadir tepat waktu demi keamanan bersama.\n\nTerima kasih atas kontribusinya menjaga lingkungan. 🙏",
            ],
            [
                'name' => 'Info Darurat/Keamanan',
                'event_key' => null,
                'content' => "🚨 *INFO KEAMANAN* 🚨\n\nWarga RT [nomor_rt]/RW [nomor_rw] dimohon waspada dan meningkatkan kewaspadaan keamanan lingkungan. Segera laporkan ke pengurus RT/RW jika ada hal mencurigakan.\n\nJaga selalu keamanan bersama. 🙏",
            ],
            [
                'name' => 'Info Pemadaman Listrik/Air',
                'event_key' => null,
                'content' => "*INFORMASI* ⚡\n\nAkan ada pemadaman listrik/air sementara di wilayah RT [nomor_rt]/RW [nomor_rw] untuk keperluan pemeliharaan. Mohon Bapak/Ibu [nama_warga] mempersiapkan diri.\n\nMohon maaf atas ketidaknyamanannya. 🙏",
            ],
            [
                'name' => 'Ucapan Selamat Hari Raya',
                'event_key' => null,
                'content' => "Assalamu'alaikum Warahmatullahi Wabarakatuh 🌙✨\n\nSegenap pengurus RW [nomor_rw] mengucapkan *Selamat Hari Raya Idul Fitri* kepada Bapak/Ibu [nama_warga]. Mohon maaf lahir dan batin.\n\nSemoga kita semua kembali fitri. Aamiin 🤲",
            ],
        ];

        foreach ($templates as $template) {
            WhatsappTemplate::factory()->create($template);
        }
    }

    /**
     * Data contoh modul Inventaris (docs/issues/001-modul-inventaris.md):
     * barang milik RW pusat & per-RT, plus riwayat peminjaman yang mencakup
     * status dipinjam, terlambat, dikembalikan, dan hilang.
     */
    private function seedInventory(User $sekretaris, User $ketuaRw, MasterRt $rt1, MasterRt $rt2, Resident $demoResident): void
    {
        $categories = collect(['Peralatan Acara', 'Elektronik', 'Kebersihan', 'Furnitur'])
            ->mapWithKeys(fn (string $name) => [$name => InventoryCategory::factory()->create(['name' => $name])]);

        $year = now()->year;
        $sequence = 0;
        $nextCode = function () use (&$sequence, $year) {
            $sequence++;

            return sprintf('INV-%d-%04d', $year, $sequence);
        };

        $itemDefinitions = [
            ['name' => 'Kursi Lipat', 'category' => 'Peralatan Acara', 'rt_id' => null, 'quantity' => 50, 'location' => 'Gudang Balai RW'],
            ['name' => 'Tenda Pesta 4x6', 'category' => 'Peralatan Acara', 'rt_id' => null, 'quantity' => 4, 'location' => 'Gudang Balai RW'],
            ['name' => 'Meja Panjang Lipat', 'category' => 'Peralatan Acara', 'rt_id' => null, 'quantity' => 10, 'location' => 'Gudang Balai RW'],
            ['name' => 'Sound System Portable', 'category' => 'Elektronik', 'rt_id' => null, 'quantity' => 2, 'location' => 'Ruang Sekretariat RW'],
            ['name' => 'Genset 2000 Watt', 'category' => 'Elektronik', 'rt_id' => null, 'quantity' => 1, 'location' => 'Gudang Balai RW', 'condition' => 'rusak_ringan', 'notes' => 'Perlu servis karburator.'],
            ['name' => 'Karpet Permadani', 'category' => 'Furnitur', 'rt_id' => null, 'quantity' => 20, 'location' => 'Gudang Balai RW'],
            ['name' => 'Terpal', 'category' => 'Kebersihan', 'rt_id' => $rt1->id, 'quantity' => 5, 'location' => 'Pos Ronda RT 001'],
            ['name' => 'Cangkul', 'category' => 'Kebersihan', 'rt_id' => $rt1->id, 'quantity' => 8, 'location' => 'Pos Ronda RT 001'],
            ['name' => 'Sapu Lidi', 'category' => 'Kebersihan', 'rt_id' => $rt2->id, 'quantity' => 15, 'location' => 'Pos Ronda RT 002'],
            ['name' => 'Gerobak Sampah', 'category' => 'Kebersihan', 'rt_id' => $rt2->id, 'quantity' => 2, 'location' => 'Pos Ronda RT 002'],
        ];

        $items = collect($itemDefinitions)->mapWithKeys(fn (array $definition) => [
            $definition['name'] => InventoryItem::factory()->create([
                'inventory_category_id' => $categories[$definition['category']]->id,
                'rt_id' => $definition['rt_id'],
                'code' => $nextCode(),
                'name' => $definition['name'],
                'quantity' => $definition['quantity'],
                'condition' => $definition['condition'] ?? 'baik',
                'location' => $definition['location'],
                'notes' => $definition['notes'] ?? null,
                'created_by' => $sekretaris->id,
            ]),
        ]);

        // Sedang dipinjam: warga demo pinjam kursi untuk hajatan.
        InventoryLoan::factory()->create([
            'inventory_item_id' => $items['Kursi Lipat']->id,
            'resident_id' => $demoResident->id,
            'borrower_name' => $demoResident->name,
            'borrower_phone' => $demoResident->phone,
            'quantity_borrowed' => 15,
            'purpose' => 'Hajatan pernikahan keluarga',
            'loan_date' => now()->subDays(2)->toDateString(),
            'due_date' => now()->addDays(3)->toDateString(),
            'status' => 'dipinjam',
            'handled_by' => $sekretaris->id,
        ]);

        // Sedang dipinjam & sudah lewat jatuh tempo (tampil "Terlambat" di laporan).
        InventoryLoan::factory()->create([
            'inventory_item_id' => $items['Sound System Portable']->id,
            'borrower_name' => 'Karang Taruna RW 001',
            'borrower_phone' => '081298765432',
            'quantity_borrowed' => 1,
            'purpose' => 'Acara pentas seni',
            'loan_date' => now()->subDays(10)->toDateString(),
            'due_date' => now()->subDays(3)->toDateString(),
            'status' => 'dipinjam',
            'handled_by' => $sekretaris->id,
        ]);

        // Riwayat: sudah dikembalikan tepat waktu dalam kondisi baik.
        InventoryLoan::factory()->create([
            'inventory_item_id' => $items['Tenda Pesta 4x6']->id,
            'borrower_name' => 'Panitia 17 Agustus RT 001',
            'borrower_phone' => '081234567890',
            'quantity_borrowed' => 1,
            'purpose' => 'Perayaan HUT RI',
            'loan_date' => now()->subDays(20)->toDateString(),
            'due_date' => now()->subDays(17)->toDateString(),
            'return_date' => now()->subDays(16)->toDateString(),
            'returned_condition' => 'baik',
            'status' => 'dikembalikan',
            'handled_by' => $ketuaRw->id,
            'notes' => 'Dikembalikan tepat waktu, kondisi baik.',
        ]);

        // Riwayat: hilang saat dipinjam — jumlah barang dikurangi permanen,
        // meniru efek samping InventoryLoanController::returnItem().
        InventoryLoan::factory()->create([
            'inventory_item_id' => $items['Cangkul']->id,
            'borrower_name' => 'Pak Slamet',
            'borrower_phone' => '081211112222',
            'quantity_borrowed' => 1,
            'purpose' => 'Kerja bakti bersih got',
            'loan_date' => now()->subDays(30)->toDateString(),
            'due_date' => now()->subDays(28)->toDateString(),
            'return_date' => now()->subDays(25)->toDateString(),
            'returned_condition' => 'hilang',
            'status' => 'hilang',
            'handled_by' => $sekretaris->id,
            'notes' => 'Dilaporkan hilang, tidak dikembalikan.',
        ]);
        $items['Cangkul']->decrement('quantity');
    }
}
