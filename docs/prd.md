# Product Requirement Document (PRD)
## Sistem Informasi Manajemen RW (SIM-RW)

**Versi Dokumen:** 1.0.0  
**Status:** Final / Siap Develop  
**Target Rilis MVP:** Q1 2026  
**Pemilik Proyek:** [Nama Anda / Tim Pengembang]

---

## 1. Pendahuluan & Tujuan Proyek

### 1.1. Latar Belakang
Administrasi tingkat RW di Indonesia masih banyak dilakukan secara manual (buku tulis atau file Excel terpisah). Hal ini menyulitkan pencarian data historis, pembuatan laporan untuk kelurahan, serta transparansi keuangan kepada warga. Dibutuhkan sebuah sistem informasi yang **mudah diinstall sendiri** (self-hosted), stabil, dan bisa bertahan dalam jangka waktu lama (5+ tahun).

### 1.2. Tujuan Produk
1. **Digitalisasi Data Base:** Menyediakan *single source of truth* untuk data Kepala Keluarga (KK) dan Penduduk.
2. **Otomatisasi Pelayanan:** Menerbitkan surat pengantar RW (Domisili, SKTM, dll.) dalam waktu < 5 menit.
3. **Transparansi Keuangan:** Menyajikan laporan kas masuk/keluar secara real-time dan akuntabel.
4. **Peningkatan Partisipasi:** Menampung aspirasi/pengaduan warga secara terstruktur dan terlacak.

### 1.3. Target Pengguna (Persona)
| Aktor | Peran & Kemampuan |
| :--- | :--- |
| **Super Admin** | Pengembang/Maintenance. Mengelola sistem, backup, dan konfigurasi server. |
| **Ketua RW** | Akses penuh semua data, menyetujui pengaduan tingkat RW, menerbitkan pengumuman. |
| **Sekretaris** | Operator utama surat-menyurat dan administrasi kependudukan. |
| **Bendahara** | Mengelola pemasukan/pengeluaran (tidak boleh mengakses/hapus data warga). |
| **Ketua RT** | Mengelola data warganya sendiri dan memvalidasi pengaduan di wilayah RT-nya. |
| **Warga (Viewer)** | Melihat pengumuman, mengecek tagihan (jika ada), dan mengirim pengaduan. |

---

## 2. Ruang Lingkup (Scope)

### 2.1. In-Scope (V1.0 MVP)
- Manajemen User & Role (RBAC).
- Master Data (Provinsi/Kota/Kecamatan/Kelurahan, RT, KK, Penduduk. ada di folder database/data) .
- Modul Surat Menyurat (template & cetak PDF).
- Modul Keuangan Sederhana (Kas Masuk/Keluar + Laporan Bulanan).
- Modul Pengumuman & Pengaduan.
- Dashboard Statistik Dasar.

### 2.2. Out-of-Scope (Rencana V2 / Roadmap)
- Integrasi API Dukcapil (pencocokan NIK secara *real-time*).
- Aplikasi Mobile Native (Android/iOS) – cukup gunakan PWA atau responsive web.
- Payment Gateway (pembayaran iuran online).
- Integrasi IoT (lampu jalan, CCTV).

---

## 3. Functional Requirements (FR)

### FR 01: Modul Manajemen Pengguna (RBAC)
| ID | Fitur | Deskripsi | Kriteria Penerimaan (AC) |
| :--- | :--- | :--- | :--- |
| FR 01.1 | Login/Logout | Autentikasi menggunakan Email/NIK dan password (hash bcrypt). | User harus login untuk akses dashboard. Gagal 3x => muncul captcha. |
| FR 01.2 | CRUD User | Admin dapat menambah/mengedit/menghapus akun pengguna. | Input wajib: Nama, Email, Password, Role (RW/RT/Sek/Bendahara/Warga). |
| FR 01.3 | Manajemen Izin | Setiap Role memiliki permission terpisah (middleware). | Ketua RT hanya bisa melihat warganya sendiri; Bendahara tidak bisa akses menu Surat. |

### FR 02: Modul Master Data Kependudukan
| ID | Fitur | Deskripsi | Kriteria Penerimaan (AC) |
| :--- | :--- | :--- | :--- |
| FR 02.1 | Data RT/RW | Setup struktur wilayah. | Wajib ada field: `nomor_rt`, `nomor_rw`, dan `ketua_rt_id`. |
| FR 02.2 | Data Kepala Keluarga (KK) | Input data KK (NIK Kepala, Alamat, RT). | Validasi NIK harus unik dan panjang 16 digit. |
| FR 02.3 | Data Penduduk | Input data anggota keluarga (NIK, Nama, TTL, Hubungan, Pekerjaan). | Sistem otomatis mengisi gender berdasarkan aturan Dukcapil (jika memungkinkan). |
| FR 02.4 | Import/Export Excel | Menggunakan Library PhpSpreadsheet. | Admin dapat upload `.xlsx` untuk migrasi data lama. Export untuk laporan ke kelurahan. |

### FR 03: Modul Surat Menyurat (Fitur Paling Kritis)
| ID | Fitur | Deskripsi | Kriteria Penerimaan (AC) |
| :--- | :--- | :--- | :--- |
| FR 03.1 | Template Surat | Membuat template menggunakan Blade + CSS (DomPDF). | Ada placeholder dinamis seperti `[nama_penduduk]`, `[nik]`, `[tanggal]` yang diganti otomatis. |
| FR 03.2 | Generate Surat | Pilih warga -> Pilih jenis surat (Domisili, SKTM, Usaha) -> Klik Cetak. | Surat langsung ter-download dalam format PDF. |
| FR 03.3 | Buku Agenda | Otomatis mencatat semua surat keluar ke database. | Menampilkan nomor surat, tanggal, jenis, dan tujuan secara kronologis. |

### FR 04: Modul Keuangan (Kas RW)
| ID | Fitur | Deskripsi | Kriteria Penerimaan (AC) |
| :--- | :--- | :--- | :--- |
| FR 04.1 | Kas Masuk | Input pemasukan (Iuran, Sumbangan, dll). | Wajib upload bukti foto (scan struk) dan pilih kategori. |
| FR 04.2 | Kas Keluar | Input pengeluaran (Pembelian alat, listrik, konsumsi). | Wajib upload bukti foto (struk pembelian). |
| FR 04.3 | Laporan Rekapitulasi | Menampilkan saldo akhir dan laporan per periode (Bulan/Tahun). | Bisa di-export ke Excel dan PDF. Ada diagram pie untuk visualisasi anggaran. |

### FR 05: Modul Pengaduan & Aspirasi Warga
| ID | Fitur | Deskripsi | Kriteria Penerimaan (AC) |
| :--- | :--- | :--- | :--- |
| FR 05.1 | Submit Pengaduan | Warga login, isi judul, deskripsi, upload foto (opsional). | Status awal: **Menunggu Verifikasi RT**. |
| FR 05.2 | Tracking Status | Status berubah: `Diteruskan ke RW` -> `Proses` -> `Selesai`. | Warga dapat melihat update status di dashboard pribadi. |
| FR 05.3 | Notifikasi | Jika selesai, sistem mengirim notifikasi (via Email atau WhatsApp). | Minimal email notifikasi ke pengadu. |

### FR 06: Modul Informasi & Pengumuman
| ID | Fitur | Deskripsi | Kriteria Penerimaan (AC) |
| :--- | :--- | :--- | :--- |
| FR 06.1 | Buat Pengumuman | RW membuat berita (judul, isi, gambar). | Bisa diatur tanggal tayang (*publish date*) dan tanggal kadaluarsa. |
| FR 06.2 | Arsip Pengumuman | Menampilkan daftar pengumuman lama secara terstruktur. | Warga tidak perlu login untuk melihat pengumuman (public view). |

### FR 07: Modul Keamanan & Jadwal (Opsional V1)
| ID | Fitur | Deskripsi | Kriteria Penerimaan (AC) |
| :--- | :--- | :--- | :--- |
| FR 07.1 | Jadwal Ronda | Input jadwal piket per RT. | Sistem otomatis mengingatkan H-1 sebelum jadwal (jika fitur notifikasi aktif). |

---

## 4. Non-Functional Requirements (NFR)
*Kunci agar aplikasi bisa dipakai bertahun-tahun tanpa ulang bangun.*

| Kategori | Parameter | Target / Standar |
| :--- | :--- | :--- |
| **Performa** | Waktu Muat Halaman | < 2 detik untuk 1.000 data dengan paginasi (menggunakan Cache & Index DB). |
| **Keamanan** | Proteksi Data | Wajib proteksi CSRF, XSS, SQL Injection (default Laravel). Password min 8 karakter. |
| **Ketersediaan** | Mode Offline | Aplikasi harus bisa berjalan di server lokal/intranet tanpa koneksi internet. |
| **Maintainability** | Struktur Kode | Gunakan **Modular Pattern** (nwidart/laravel-modules) agar modul tidak saling ketergantungan. |
| **Backup** | Otomatisasi | Admin bisa melakukan backup database (mysqldump) via CLI atau antarmuka admin. |
| **Upgrade** | Dependency | Gunakan PHP 8.1+ & Laravel 10/11 (LTS). Hindari package yang sudah > 2 tahun tidak di-maintain. |

---

## 5. Teknologi yang Disarankan (Boring Stack)

| Lapisan | Teknologi | Alasan |
| :--- | :--- | :--- |
| **Backend** | PHP 8.2 + Laravel 11 | Dokumentasi melimpah, developer mudah dicari di Indonesia. Support hingga 2027+. |
| **Database** | MySQL | Stabil, mendukung JSON column untuk fleksibilitas data tambahan. |
| **Frontend Stack** | React.js + Inertia.js + Tailwind CSS (dengan Vite) | SPA-like experience tanpa perlu bangun REST API terpisah; Inertia menjembatani routing/controller Laravel langsung ke komponen React. |
| **UI Components** | Headless UI / shadcn-style components (dibangun di atas Tailwind) | Komponen reusable (table, modal, form) yang konsisten dan mudah di-maintain lintas modul. |
| **PDF Generator** | Laravel-DomPDF (Barryvdh) | Paling populer dan mudah menangani UTF-8 (Huruf Indonesia). |
| **Excel** | Laravel-Excel (Maatwebsite) | Untuk import/export data warga yang masif. |

---

## 6. Persyaratan Keamanan & Privasi Data
Karena aplikasi ini menyimpan data sensitif (NIK dan alamat), maka wajib memenuhi:

1. **Kebijakan Privasi:** Tampilkan halaman kebijakan privasi di halaman login yang menyatakan data NIK hanya digunakan untuk administrasi RW dan tidak akan dijual ke pihak ketiga.
2. **Pemisahan Data (Tenant):** Setiap Ketua RT **tidak boleh** melihat data warga di RT lain (implementasi dengan Global Scope di Eloquent).
3. **Log Aktivitas (Audit Trail):** Wajib mencatat *Activity Log* (siapa, login jam berapa, dan aksi apa yang dilakukan) untuk kebutuhan audit internal.

---

## 7. Pedoman UI / UX

- **Layout:** Sidebar kiri yang responsif (collapse otomatis di HP).
- **Dashboard Utama:** Menampilkan 4 kartu statistik (Total KK, Total Penduduk, Total Saldo Kas, Total Pengaduan Pending).
- **Grafik:** Gunakan **Chart.js** untuk visualisasi demografi (Piramida Penduduk) dan keuangan.
- **Aksesibilitas:** Ukuran font minimal 14px untuk kemudahan pengguna lansia (Ketua RW seringkali berusia di atas 50 tahun).

---

## 8. Rencana Implementasi Bertahap (Roadmap)

| Fase | Durasi | Modul yang Dikerjakan | Prioritas |
| :--- | :--- | :--- | :--- |
| **Fase 1 (MVP)** | 4-6 Minggu | Setup Server, RBAC Login, Master Data (RT/KK/Penduduk), Surat Menyurat (cetak PDF). | **Wajib** |
| **Fase 2** | 3 Minggu | Modul Keuangan (Kas + Laporan), Modul Pengaduan Warga. | **Wajib** |
| **Fase 3** | 2 Minggu | Dashboard Statistik, Import/Export Excel, Pengumuman Publik. | **High** |
| **Fase 4 (V2)** | 1 Bulan (Tahun Depan) | Modul Siskamling, Inventaris, Notifikasi WhatsApp Gateway, dan PWA. | **Enhancement** |

---

## 9. Kriteria Penerimaan Akhir (Exit Criteria / Go-Live)

Aplikasi dinyatakan **Lolos Uji dan Siap Go-Live** jika memenuhi:
1. Admin berhasil mengimpor 500+ data penduduk menggunakan Excel tanpa error.
2. Sekretaris dapat membuat surat domisili dari halaman detail penduduk dalam < 3 klik.
3. Bendahara dapat melihat sisa saldo kas yang akurat berdasarkan periode bulanan.
4. Warga dapat mengirim pengaduan dan melihat statusnya berubah sesuai alur.
5. Semua test case keamanan dasar (SQL Injection, XSS) terlewati (menggunakan proteksi default Laravel).

---

## 10. Risiko dan Mitigasi

| Risiko | Dampak | Mitigasi |
| :--- | :--- | :--- |
| **Kehilangan data akibat server mati** | Tinggi | Wajib ada cronjob untuk backup database harian ke folder terpisah (atau Google Drive via API). |
| **Ketua RT/Warga lupa password** | Sedang | Sediakan fitur "Lupa Password" via email (jika email terdaftar). |
| **Developer baru tidak paham struktur modular** | Sedang | Buat `README.md` yang sangat detail dan tambahkan `php artisan module:make` cheatsheet. |
| **Perubahan regulasi format surat** | Rendah | Buat fitur *Template Editor* agar admin bisa mengubah format surat tanpa ubah kode. |

---

## 11. Lampiran / Catatan Teknis untuk Developer

- **Struktur Database Awal:** Disarankan membuat table `master_rw`, `master_rt`, `residents`, `family_heads`, `letters`, `treasuries`, `complaints`.
- **Command Installer:** Buat custom Artisan command `php artisan rw:install` untuk menjalankan migrasi, seeder, dan mengecek koneksi .env secara otomatis.
- **Git Workflow:** Gunakan `develop` sebagai branch utama untuk testing, dan `main` untuk production. Terapkan CI/CD sederhana jika memungkinkan.

---

**Dokumen ini adalah panduan resmi pengembangan. Setiap perubahan signifikan pada ruang lingkup harus melalui revisi dokumen dan persetujuan bersama.**
