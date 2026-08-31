<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

/**
 * Panduan penggunaan REST API SIM-RW (docs/issues/002-rest-api.md), hanya
 * untuk Super Admin — audiens utamanya adalah developer aplikasi mobile
 * warga, bukan pengguna CMS sehari-hari.
 */
class ApiGuideController extends Controller
{
    /**
     * @var array<int, array{name: string, base: string, roles: string, endpoints: array<int, array{method: string, path: string, desc: string}>}>
     */
    private const GROUPS = [
        [
            'name' => 'Autentikasi',
            'base' => '/api/v1',
            'roles' => 'Semua peran',
            'endpoints' => [
                ['method' => 'POST', 'path' => '/login', 'desc' => 'Login email+password, menerbitkan token.'],
                ['method' => 'POST', 'path' => '/logout', 'desc' => 'Mencabut token yang sedang dipakai.'],
                ['method' => 'GET', 'path' => '/user', 'desc' => 'Profil pengguna yang sedang login.'],
            ],
        ],
        [
            'name' => 'Dashboard & Data Saya',
            'base' => '/api/v1',
            'roles' => 'Semua peran (field tertentu dibatasi per peran)',
            'endpoints' => [
                ['method' => 'GET', 'path' => '/dashboard', 'desc' => 'Ringkasan statistik.'],
                ['method' => 'GET', 'path' => '/data-saya', 'desc' => 'Data kependudukan milik sendiri (warga).'],
                ['method' => 'PUT', 'path' => '/data-saya', 'desc' => 'Perbarui kontak sendiri (warga).'],
            ],
        ],
        [
            'name' => 'Master Data: Wilayah & RW',
            'base' => '/api/v1',
            'roles' => 'Super Admin, Ketua RW',
            'endpoints' => [
                ['method' => 'GET', 'path' => '/wilayah/provinces|districts|subdistricts|villages', 'desc' => 'Dropdown wilayah berjenjang.'],
                ['method' => 'GET', 'path' => '/rw', 'desc' => 'Profil RW.'],
                ['method' => 'PUT', 'path' => '/rw', 'desc' => 'Perbarui profil RW.'],
                ['method' => 'GET/POST/PUT/DELETE', 'path' => '/rt', 'desc' => 'Kelola data RT.'],
            ],
        ],
        [
            'name' => 'Kepala Keluarga & Penduduk',
            'base' => '/api/v1',
            'roles' => 'Super Admin, Ketua RW, Sekretaris, Ketua RT (terisolasi per RT)',
            'endpoints' => [
                ['method' => 'GET/POST/PUT/DELETE', 'path' => '/family-heads', 'desc' => 'Kelola data KK.'],
                ['method' => 'POST', 'path' => '/family-heads/{familyHead}/residents', 'desc' => 'Tambah anggota keluarga.'],
                ['method' => 'PUT/DELETE', 'path' => '/residents/{resident}', 'desc' => 'Ubah/hapus data penduduk.'],
                ['method' => 'GET', 'path' => '/residents/search?q=', 'desc' => 'Pencarian cepat (NIK/nama).'],
                ['method' => 'GET', 'path' => '/residents-export', 'desc' => 'Unduh Excel data penduduk.'],
                ['method' => 'POST', 'path' => '/residents-import', 'desc' => 'Impor Excel data penduduk.'],
            ],
        ],
        [
            'name' => 'Surat Menyurat',
            'base' => '/api/v1',
            'roles' => 'Super Admin, Ketua RW, Sekretaris',
            'endpoints' => [
                ['method' => 'GET/POST/PUT/DELETE', 'path' => '/letter-templates', 'desc' => 'Kelola template surat.'],
                ['method' => 'GET/POST', 'path' => '/letters', 'desc' => 'Daftar & terbitkan surat.'],
                ['method' => 'GET', 'path' => '/letters/{letter}/download', 'desc' => 'Unduh PDF surat.'],
            ],
        ],
        [
            'name' => 'Keuangan (Kas)',
            'base' => '/api/v1',
            'roles' => 'Super Admin, Ketua RW, Bendahara',
            'endpoints' => [
                ['method' => 'GET/POST/PUT/DELETE', 'path' => '/treasury-categories', 'desc' => 'Kelola kategori kas.'],
                ['method' => 'GET/POST/PUT/DELETE', 'path' => '/treasuries', 'desc' => 'Kas masuk/keluar (upload proof_photo).'],
                ['method' => 'GET', 'path' => '/treasury-report', 'desc' => 'Ringkasan laporan kas.'],
                ['method' => 'GET', 'path' => '/treasury-report/export-excel|export-pdf', 'desc' => 'Unduh laporan.'],
            ],
        ],
        [
            'name' => 'Pengaduan',
            'base' => '/api/v1',
            'roles' => 'Super Admin, Ketua RW, Ketua RT, Warga',
            'endpoints' => [
                ['method' => 'GET/POST', 'path' => '/complaints', 'desc' => 'Daftar (milik sendiri utk warga) & submit pengaduan.'],
                ['method' => 'GET', 'path' => '/complaints/{complaint}', 'desc' => 'Detail pengaduan.'],
                ['method' => 'PATCH', 'path' => '/complaints/{complaint}/status', 'desc' => 'Ubah status (RT/RW).'],
            ],
        ],
        [
            'name' => 'Pengumuman',
            'base' => '/api/v1',
            'roles' => 'Semua peran melihat; Super Admin/Ketua RW mengelola',
            'endpoints' => [
                ['method' => 'GET', 'path' => '/announcements', 'desc' => 'Daftar (warga hanya melihat yang tayang).'],
                ['method' => 'POST/PUT/DELETE', 'path' => '/announcements', 'desc' => 'Kelola pengumuman.'],
            ],
        ],
        [
            'name' => 'Inventaris',
            'base' => '/api/v1',
            'roles' => 'Super Admin, Ketua RW, Sekretaris, Ketua RT (terisolasi per RT)',
            'endpoints' => [
                ['method' => 'GET/POST/PUT/DELETE', 'path' => '/inventory-categories', 'desc' => 'Kelola kategori (bukan Ketua RT).'],
                ['method' => 'GET/POST/PUT/DELETE', 'path' => '/inventory-items', 'desc' => 'Kelola barang inventaris.'],
                ['method' => 'GET/POST', 'path' => '/inventory-loans', 'desc' => 'Catat & lihat peminjaman.'],
                ['method' => 'PATCH', 'path' => '/inventory-loans/{inventoryLoan}/return', 'desc' => 'Catat pengembalian.'],
                ['method' => 'GET', 'path' => '/inventory-report', 'desc' => 'Laporan kondisi & peminjaman.'],
            ],
        ],
        [
            'name' => 'WhatsApp',
            'base' => '/api/v1',
            'roles' => 'Super Admin, Ketua RW',
            'endpoints' => [
                ['method' => 'GET/POST', 'path' => '/whatsapp-broadcast', 'desc' => 'Kirim & lihat riwayat broadcast.'],
                ['method' => 'GET/POST/PUT/DELETE', 'path' => '/whatsapp-templates', 'desc' => 'Kelola template pesan.'],
            ],
        ],
    ];

    public function __invoke(): Response
    {
        return Inertia::render('ApiGuide/Index', [
            'groups' => self::GROUPS,
        ]);
    }
}
