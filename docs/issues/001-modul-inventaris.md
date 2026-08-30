# Issue #001 — Modul Inventaris

**Status:** Selesai (v1) — lihat migrasi `2026_08_30_150001`–`150003`, `App\Http\Controllers\Inventory*`, dan `tests/Feature/Inventory`.
**Terkait:** [`docs/prd.md`](../prd.md) Bagian 8 — Fase 4 (V2), "Modul Siskamling, Inventaris, Notifikasi WhatsApp Gateway, dan PWA"
**Prasyarat:** Tidak ada — modul berdiri sendiri, tidak bergantung pada modul V2 lain di Fase 4.

> PRD hanya menyebut "Inventaris" sebagai satu baris roadmap tanpa detail FR. Dokumen ini mengisi kekosongan tersebut sebelum implementasi dimulai, mengikuti format FR/AC yang sama dengan Bagian 3 PRD.

---

## 1. Latar Belakang

RW/RT umumnya memiliki barang inventaris bersama (tenda, kursi, sound system, alat kebersihan, dsb.) yang dipinjamkan ke warga untuk acara (hajatan, kerja bakti, rapat). Saat ini pencatatan barang & peminjaman dilakukan manual (buku catatan), sehingga rawan hilang tanpa jejak, tidak ada catatan siapa terakhir meminjam, dan pengurus baru sulit melakukan serah-terima aset.

## 2. Tujuan

1. Satu sumber data untuk seluruh barang inventaris RW/RT beserta kondisinya.
2. Mencatat riwayat peminjaman-pengembalian agar barang yang belum kembali/rusak/hilang dapat dilacak siapa yang terakhir memegangnya.
3. Menyediakan laporan ringkas kondisi aset untuk serah-terima kepengurusan.

## 3. Ruang Lingkup

### 3.1. In-Scope
- CRUD data barang inventaris (per kategori, per pemilik RW/RT).
- Pencatatan kode inventaris unik per barang.
- Alur peminjaman: ajukan/catat pinjam → tandai kembali → update kondisi saat kembali.
- Laporan status barang (tersedia, dipinjam, rusak, hilang) dan riwayat peminjaman per barang.

### 3.2. Out-of-Scope (dorong ke iterasi berikutnya jika dibutuhkan)
- Approval berjenjang untuk peminjaman (mengikuti pola verifikasi Pengaduan FR05) — v1 cukup dicatat langsung oleh Sekretaris/Ketua RT.
- Biaya sewa/denda keterlambatan — RW pada umumnya meminjamkan gratis ke warga; jika ada RW yang memungut, ini didorong ke iterasi berikut agar tidak menambah kompleksitas kas (lihat FR04).
- Notifikasi WhatsApp otomatis H-1 jatuh tempo pengembalian (bisa dipetakan ke `WhatsappTemplate` yang sudah ada, tapi bukan syarat rilis awal modul ini).
- QR code / barcode fisik pada barang.

## 4. Functional Requirements (FR 08)

| ID | Fitur | Deskripsi | Kriteria Penerimaan (AC) |
| :--- | :--- | :--- | :--- |
| FR 08.1 | Kategori Barang | Kelola kategori inventaris (mis. Peralatan Acara, Elektronik, Kebersihan). | Sekretaris/Ketua RW dapat CRUD kategori; kategori tidak bisa dihapus jika masih dipakai barang. |
| FR 08.2 | Data Barang Inventaris | Input barang: nama, kategori, kode inventaris, jumlah, kondisi, lokasi simpan, foto, kepemilikan (RW pusat atau RT tertentu). | Kode inventaris unik & digenerate otomatis (mis. `INV-2026-0001`). Validasi jumlah > 0. |
| FR 08.3 | Peminjaman Barang | Catat peminjaman: barang, peminjam (warga terdaftar atau nama bebas + no. HP), jumlah, keperluan, tanggal pinjam, rencana kembali. | Jumlah yang dipinjam tidak boleh melebihi stok tersedia (stok tersedia = jumlah − yang sedang dipinjam). Status awal: **Dipinjam**. |
| FR 08.4 | Pengembalian & Kondisi | Tandai barang kembali, update kondisi hasil pengembalian (baik/rusak ringan/rusak berat/hilang) dan catatan. | Saat status jadi **Dikembalikan**/**Hilang**, stok tersedia barang otomatis ter-update sesuai kondisi (barang hilang mengurangi jumlah total). |
| FR 08.5 | Laporan Inventaris | Dashboard ringkas: total barang per kategori, barang sedang dipinjam, barang rusak/hilang, dan riwayat peminjaman per barang. | Bisa difilter per RT (untuk Ketua RT hanya melihat wilayahnya, mengikuti pola isolasi data FR di Bagian 6.2 PRD). Export Excel opsional, konsisten dengan pola FR02.4. |

## 5. Peran & Hak Akses

Mengikuti matriks peran di PRD Bagian 1.3 dan konvensi middleware `role:` yang sudah dipakai di modul lain (`routes/web.php`):

| Peran | Hak Akses |
| :--- | :--- |
| **Super Admin** | Akses penuh (mengikuti pola modul lain). |
| **Ketua RW** | CRUD kategori & barang milik RW pusat maupun seluruh RT, kelola peminjaman, lihat laporan semua wilayah. |
| **Sekretaris** | CRUD kategori & barang, kelola peminjaman/pengembalian (operator harian — sejalan dengan perannya di FR03 Surat Menyurat). |
| **Bendahara** | Tidak ada akses (di luar tanggung jawabnya, sama seperti larangan akses data warga pada PRD Bagian 1.3). |
| **Ketua RT** | Lihat & kelola barang milik RT-nya sendiri, catat peminjaman untuk warganya; tidak bisa melihat/mengubah barang RT lain (global scope, sama seperti `family_heads`/`complaints`). |
| **Warga** | Tidak ada menu CMS; opsional tampil sebagai riwayat pada halaman "Data Saya" jika sedang meminjam sesuatu (dorong ke iterasi berikut, bukan syarat awal). |

## 6. Skema Data (Usulan)

Mengikuti konvensi penamaan `snake_case` plural pada [`docs/erd.md`](../erd.md) dan pola atribut PHP `#[Fillable]` yang dipakai di seluruh model existing.

```
inventory_categories
  id            bigint PK
  name          string
  timestamps

inventory_items
  id                      bigint PK
  inventory_category_id   bigint FK -> inventory_categories.id
  rt_id                   bigint FK -> master_rt.id, nullable (null = milik RW pusat)
  code                    string UK   "kode inventaris, auto-generate"
  name                    string
  quantity                unsignedInteger  "jumlah total dimiliki"
  condition               enum "baik|rusak_ringan|rusak_berat|hilang"
  location                string  "lokasi penyimpanan"
  photo                   string nullable
  notes                   string nullable
  created_by              bigint FK -> users.id
  timestamps

inventory_loans
  id                  bigint PK
  inventory_item_id   bigint FK -> inventory_items.id
  resident_id         bigint FK -> residents.id, nullable  "peminjam terdaftar"
  borrower_name        string  "nama peminjam (fallback bila bukan warga terdaftar)"
  borrower_phone        string nullable
  quantity_borrowed    unsignedInteger
  purpose              string  "keperluan peminjaman"
  loan_date            date
  due_date             date  "rencana kembali"
  return_date          date nullable
  returned_condition   enum "baik|rusak_ringan|rusak_berat|hilang", nullable
  status               enum "dipinjam|dikembalikan|terlambat|hilang"
  handled_by           bigint FK -> users.id  "yang mencatat peminjaman"
  notes                string nullable
  timestamps
```

Catatan desain:
- `inventory_items.rt_id` nullable mengikuti pola yang sama seperti `whatsapp_broadcasts.rt_id` (null = lingkup RW, terisi = milik RT tertentu) — bukan pola baru bagi codebase ini.
- `inventory_loans.resident_id` nullable + kolom `borrower_name`/`borrower_phone` sebagai fallback, karena tidak semua peminjam adalah warga dengan akun/NIK terdaftar (mis. panitia acara dari luar RT, atau warga yang belum tercatat sebagai anggota KK).
- Status `terlambat` dihitung otomatis dari `due_date < now()` dan `status = dipinjam` saat query list (bukan disimpan via scheduler terpisah) — konsisten dengan pola sederhana yang sudah dipakai di modul lain, tanpa menambah cron job baru.

## 7. Alur Peminjaman (Status)

```
Dipinjam → Dikembalikan   (kondisi dicatat saat kembali)
Dipinjam → Terlambat      (otomatis, due_date lewat & belum kembali — bukan status tersimpan permanen, hilang begitu status berubah)
Dipinjam → Hilang          (dicatat manual oleh Sekretaris/Ketua RT/RW jika barang tidak kembali)
```

## 8. Kriteria Penerimaan Akhir (Definition of Done)

1. Sekretaris dapat menambah kategori & barang baru, kode inventaris ter-generate otomatis dan unik.
2. Sekretaris/Ketua RT dapat mencatat peminjaman; sistem menolak jika jumlah pinjam melebihi stok tersedia.
3. Menandai barang kembali memperbarui stok tersedia dan kondisi barang sesuai input.
4. Ketua RT hanya melihat & mengelola barang serta peminjaman milik RT-nya (isolasi data via global scope, konsisten dengan modul lain).
5. Bendahara tidak memiliki akses ke menu ini sama sekali (403 di route level).
6. Laporan inventaris menampilkan jumlah barang per status (tersedia/dipinjam/rusak/hilang) yang akurat terhadap data transaksi.
7. Test suite (`php artisan test --compact`) mencakup RBAC, validasi stok, dan alur pinjam-kembali; seluruh suite tetap hijau.

## 9. Pertanyaan Terbuka

- Apakah barang milik RW pusat (`rt_id = null`) boleh dipinjam warga dari RT manapun, atau hanya oleh pengurus RW? (Asumsi sementara: siapa saja, dicatat oleh Sekretaris/Ketua RW.)
- Apakah perlu foto bukti saat pengembalian (mirip `proof_photo` pada modul Keuangan)? (tidak perlu, hanya perlu ada laporan saja ketika dipinjam berapa dan kembali berapa).
