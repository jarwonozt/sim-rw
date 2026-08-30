<p align="center">
  <img src="https://api.iconify.design/heroicons:cube-transparent-20-solid.svg?color=%230d7c66&height=64" width="64" height="64" alt="SIM-RW">
</p>

<h1 align="center">SIM-RW</h1>
<p align="center"><strong>Sistem Informasi Manajemen RW</strong> — digitalisasi administrasi kependudukan, surat-menyurat, keuangan, dan pengaduan warga tingkat Rukun Warga.</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.3%2B-777bb4?logo=php&logoColor=white" alt="PHP 8.3+">
  <img src="https://img.shields.io/badge/Laravel-13-ff2d20?logo=laravel&logoColor=white" alt="Laravel 13">
  <img src="https://img.shields.io/badge/React-18-61dafb?logo=react&logoColor=white" alt="React 18">
  <img src="https://img.shields.io/badge/Inertia.js-2-9553e9?logo=inertia&logoColor=white" alt="Inertia.js 2">
  <img src="https://img.shields.io/badge/Tailwind%20CSS-4-38bdf8?logo=tailwindcss&logoColor=white" alt="Tailwind CSS 4">
  <img src="https://img.shields.io/badge/tests-passing-2ea043" alt="Tests passing">
</p>

---

## Tentang Proyek

Administrasi tingkat RW di Indonesia masih banyak dikerjakan lewat buku tulis dan file Excel yang tercecer — menyulitkan pencarian data historis, penyusunan laporan ke kelurahan, dan transparansi keuangan ke warga. **SIM-RW** adalah sistem informasi *self-hosted* yang menggantikan proses manual tersebut dengan satu aplikasi terpadu, dibangun mengikuti spesifikasi lengkap di [`docs/prd.md`](docs/prd.md) dan skema data di [`docs/erd.md`](docs/erd.md).

## Fitur Utama

| Modul | Deskripsi |
| :--- | :--- |
| **Manajemen Pengguna (RBAC)** | 6 peran (Super Admin, Ketua RW, Sekretaris, Bendahara, Ketua RT, Warga) dengan hak akses terpisah per middleware. |
| **Master Data Kependudukan** | Data wilayah RT/RW, Kepala Keluarga, dan Penduduk — isolasi data otomatis per RT lewat *global scope* Eloquent. Import/export massal via Excel. |
| **Surat Menyurat** | Template surat dengan placeholder dinamis, penerbitan PDF (DomPDF) dengan nomor agenda otomatis, dan buku agenda kronologis. |
| **Keuangan (Kas RW)** | Pencatatan kas masuk/keluar dengan bukti foto wajib, laporan rekapitulasi per periode, grafik alokasi anggaran, export Excel & PDF. |
| **Pengaduan Warga** | Alur status berjenjang (Menunggu Verifikasi RT → Diteruskan RW → Proses → Selesai) dengan riwayat serta notifikasi otomatis via Email dan WhatsApp (Fonnte) saat pengaduan selesai. |
| **Pengumuman** | Publikasi pengumuman dengan tanggal tayang/kadaluarsa; arsip publik dapat diakses tanpa login. |
| **Broadcast & Template WhatsApp** | Kirim pesan massal ke penduduk (semua RT atau RT tertentu) via Fonnte, dengan template pesan siap-pakai dan riwayat pengiriman. Template yang sama juga bisa dipasang otomatis ke notifikasi sistem (mis. pengaduan selesai). |
| **Data Saya (Warga)** | Warga login dan memperbarui data kontaknya sendiri (No. HP, pekerjaan, pendidikan, dll) tanpa perlu lewat pengurus RW; data identitas resmi tetap terkunci. |
| **Inventaris** | Kelola barang inventaris RW/RT (kode otomatis, kategori, kondisi), catat peminjaman-pengembalian warga dengan validasi stok, dan laporan ringkas per kategori/RT. Lihat [`docs/issues/001-modul-inventaris.md`](docs/issues/001-modul-inventaris.md). |
| **Dashboard** | Statistik ringkas, piramida penduduk, tren kas bulanan, alokasi anggaran, dan log aktivitas — semua otomatis menyesuaikan hak akses peran yang login. |

## Tumpukan Teknologi

| Lapisan | Teknologi |
| :--- | :--- |
| Backend | PHP 8.3, Laravel 13 |
| Frontend | React 18 + Inertia.js 2 + Tailwind CSS 4 |
| Database | SQLite (default lokal) / MySQL & MariaDB (produksi) |
| PDF | barryvdh/laravel-dompdf |
| Excel | maatwebsite/excel (PhpSpreadsheet) |
| WhatsApp Gateway | [Fonnte](https://fonnte.com) |
| Build Tool | Vite |

## Prasyarat

- PHP ^8.3 dengan ekstensi standar Laravel
- Composer 2
- Node.js ^20 dan npm
- SQLite (bawaan) atau MySQL/MariaDB bila ingin dijalankan di luar SQLite

## Instalasi

```bash
git clone <url-repository> sim-rw
cd sim-rw

composer install
npm install

cp .env.example .env
php artisan key:generate

touch database/database.sqlite   # jika memakai SQLite (default)
php artisan migrate --seed       # mengimpor data wilayah Indonesia + data demo

php artisan storage:link
```

Migrasi `--seed` menjalankan dua seeder:

- **WilayahSeeder** — mengimpor data Provinsi/Kabupaten-Kota/Kecamatan/Kelurahan resmi dari `database/data/*.json` (±93 ribu baris).
- **DemoSeeder** — membuat satu RW contoh dengan 2 RT, data KK/Penduduk, template surat, kategori kas, serta akun demo untuk setiap peran (lihat tabel di bawah).

### Notifikasi WhatsApp (opsional)

Notifikasi pengaduan selesai bisa terkirim otomatis via WhatsApp memakai [Fonnte](https://fonnte.com). Isi variabel berikut di `.env` dengan token device Fonnte milikmu:

```env
FONNTE_TOKEN=isi-token-fonnte-di-sini
```

Bila `FONNTE_TOKEN` kosong, pengiriman WhatsApp otomatis dilewati (dicatat di log) tanpa mengganggu alur aplikasi — notifikasi email tetap berjalan seperti biasa.

## Menjalankan Aplikasi

Jalankan server, queue listener, dan Vite dev server sekaligus:

```bash
composer run dev
```

Atau secara terpisah:

```bash
php artisan serve
npm run dev
```

Aplikasi dapat diakses di `http://localhost:8000`.

## Akun Demo

Semua akun memakai password **`password`**.

| Email | Peran |
| :--- | :--- |
| `superadmin@sim-rw.test` | Super Admin |
| `ketuarw@sim-rw.test` | Ketua RW |
| `sekretaris@sim-rw.test` | Sekretaris |
| `bendahara@sim-rw.test` | Bendahara |
| `ketuart1@sim-rw.test` | Ketua RT 001 |
| `ketuart2@sim-rw.test` | Ketua RT 002 |
| `warga@sim-rw.test` | Warga |

## Pengujian & Kualitas Kode

```bash
php artisan test              # jalankan seluruh test suite (PHPUnit)
vendor/bin/pint                # cek format kode
vendor/bin/pint --dirty        # format ulang file yang berubah
```

## Dokumentasi

- [`docs/prd.md`](docs/prd.md) — Product Requirement Document lengkap (latar belakang, ruang lingkup, functional & non-functional requirements, roadmap).
- [`docs/erd.md`](docs/erd.md) — Entity Relationship Diagram skema database.

## Roadmap

Fase 1–3 (Master Data, Surat Menyurat, Keuangan, Pengaduan & Pengumuman) sudah terimplementasi, termasuk notifikasi WhatsApp yang semula direncanakan di Fase 4. Sisa rencana Fase 4/V2 meliputi modul Siskamling, Inventaris, dan dukungan PWA — lihat Bagian 8 pada [`docs/prd.md`](docs/prd.md) untuk detail.

## Lisensi

Proyek ini dikembangkan untuk kebutuhan internal pengurus RW dan dibangun di atas [framework Laravel](https://laravel.com) yang bersifat open-source (MIT).
