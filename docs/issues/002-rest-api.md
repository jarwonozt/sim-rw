# Issue #002 — REST API (Sanctum) untuk Aplikasi Mobile Warga

**Status:** Selesai (v1) — lihat `routes/api.php`, `App\Http\Controllers\Api\V1\*`, `tests/Feature/Api`, dan panduan pemakaian di [`docs/api-guide.md`](../api-guide.md). Dokumentasi interaktif (Scramble) tersedia di `/docs/api` untuk Super Admin, juga dari menu **Panduan API** di dashboard.
**Terkait:** [`docs/prd.md`](../prd.md) Bagian 5 (Teknologi) & Bagian 8 — Fase 4 (V2), "...dan PWA"; [`docs/issues/001-modul-inventaris.md`](001-modul-inventaris.md) (modul yang ikut diekspos).
**Prasyarat:** Tidak ada migrasi baru yang memblokir — hanya menambah tabel bawaan Sanctum (`personal_access_tokens`) di atas skema yang sudah ada.

> PRD Bagian 5 secara eksplisit memilih Inertia agar **tidak perlu membangun REST API terpisah** untuk dashboard web. Dokumen ini membuka cakupan baru di luar keputusan itu: menyediakan REST API sebagai fondasi untuk **aplikasi mobile warga** (klien native/PWA yang tidak bisa memakai sesi Inertia), sekaligus mengekspos seluruh modul CMS agar bisa dipakai peran lain (pengurus RW/RT) di masa depan tanpa membangun API lagi dari nol. Dashboard web (Inertia) **tidak diubah** dan tetap menjadi klien utama — API ini berjalan berdampingan (`routes/api.php`), bukan pengganti.

---

## 1. Latar Belakang

Saat ini satu-satunya cara mengakses SIM-RW adalah dashboard web berbasis sesi (Inertia + cookie). Warga yang ingin melihat pengumuman, status pengaduan, atau status peminjaman barang dari HP harus membuka browser dan login lewat form web. PRD Bagian 8 (Fase 4) sudah mencantumkan "PWA" sebagai rencana V2, dan `laravel/sanctum` sudah terpasang sebagai dependency sejak awal proyek (`composer.json`) namun belum dikonfigurasi (`HasApiTokens` belum dipakai di `User`, `routes/api.php` belum ada, `config/sanctum.php` belum dipublish).

Membangun REST API sekarang meletakkan fondasi autentikasi berbasis token yang dibutuhkan aplikasi mobile, sekaligus — karena scope yang diminta mencakup seluruh modul CMS — memungkinkan pengurus (Sekretaris/Bendahara/Ketua RT/RW) mengelola data dari luar browser di iterasi berikutnya, tanpa desain ulang.

## 2. Tujuan

1. Menyediakan autentikasi berbasis token (Sanctum personal access token) yang independen dari sesi web, untuk klien mobile/native.
2. Mengekspos seluruh modul CMS yang sudah ada di dashboard web (Master Data, Surat, Keuangan, Pengaduan, Pengumuman, Inventaris, WhatsApp) melalui endpoint REST versi `v1`, dengan RBAC yang identik dengan `routes/web.php`.
3. Menjaga dashboard web (Inertia) tetap sebagai satu-satunya *source of truth* untuk logika bisnis — API memanggil ulang Model/FormRequest/Policy yang sama, bukan menduplikasi aturan.

## 3. Ruang Lingkup

### 3.1. In-Scope

- **Autentikasi:** `POST /api/v1/login` (email + password → token), `POST /api/v1/logout` (revoke token aktif), `GET /api/v1/user` (profil user yang sedang login beserta relasi `resident`).
- **Paritas modul dengan web**, mengikuti RBAC per peran yang sama persis dengan `routes/web.php`:
  - Master Data: Wilayah (provinsi/kota/kecamatan/kelurahan read-only), RT, Kepala Keluarga, Penduduk, pencarian penduduk.
  - Surat Menyurat: template surat, daftar/detail/generate surat, download PDF.
  - Keuangan: kategori kas, kas masuk/keluar (termasuk upload `proof_photo`), laporan kas (+ export Excel/PDF).
  - Pengaduan: submit (+ upload `photo`), tracking status, update status oleh RT/RW.
  - Pengumuman: CRUD (RW), daftar pengumuman publik tanpa token (menggantikan kebutuhan endpoint publik terpisah).
  - Inventaris: kategori, barang (+ upload `photo`), peminjaman, pengembalian, laporan.
  - WhatsApp: broadcast, template.
  - Dashboard: satu endpoint ringkasan (`GET /api/v1/dashboard`) mereplikasi `DashboardController` (statistik, tren kas, piramida penduduk — masing-masing tunduk pada RBAC per field seperti versi web).
  - "Data Saya": warga melihat & memperbarui data kontaknya sendiri.
- **Response envelope konsisten** (lihat Bagian 6) dan **Eloquent API Resources** untuk setiap resource (sesuai konvensi Laravel Boost — bukan `$model->toJson()` mentah).
- **Rate limiting** default `throttle:api` (60 req/menit per token) mengikuti bawaan Sanctum.

### 3.2. Out-of-Scope (dorong ke iterasi berikutnya)

- **Push notification** (FCM/APNs) ke aplikasi mobile — di luar cakupan REST API murni.
- **Registrasi mandiri via API** — pembuatan akun tetap lewat admin/CMS (pola existing: `User` dibuat lalu ditautkan ke `Resident`), API hanya menangani login.
- **Refresh token / rotasi / masa kedaluwarsa token** — pakai token statis bawaan Sanctum dulu (revoke manual via logout); kebijakan expiry didorong ke iterasi berikutnya jika ditemukan kebutuhan keamanan tambahan.
- **OAuth2 / login sosial** — cukup email + password, paritas dengan web (FR01.1).
- ~~Dokumentasi interaktif (OpenAPI/Swagger UI)~~ — ditambahkan lebih awal dari rencana memakai [Scramble](https://scramble.dedoc.co/) (`/docs/api`, khusus Super Admin) karena biayanya rendah (auto-generate dari kode, bukan ditulis manual).
- **Sinkronisasi offline / cache lokal sisi klien** — di luar tanggung jawab backend.
- **Endpoint GraphQL atau webhook keluar** — hanya REST.

## 4. Functional Requirements (FR API)

| ID | Fitur | Deskripsi | Kriteria Penerimaan (AC) |
| :--- | :--- | :--- | :--- |
| FR API.1 | Autentikasi Token | Login via email+password mengembalikan Sanctum personal access token; logout me-revoke token yang sedang dipakai. | Kredensial salah → `422` dengan pesan error standar Laravel. Token valid dikirim via header `Authorization: Bearer <token>`. Akun `is_active = false` ditolak dengan pesan yang sama seperti guard web. |
| FR API.2 | Profil & Data Saya | `GET /api/v1/user` mengembalikan user + resident terkait; warga bisa update kontak lewat `PUT /api/v1/data-saya`. | Payload & validasi identik dengan `ResidentProfileController`/`UpdateOwnResidentRequest` di web. |
| FR API.3 | Master Data (Wilayah, RT, KK, Penduduk) | Endpoint list/detail/CRUD sesuai peran, isolasi data Ketua RT tetap berlaku (global scope `RtOwnedScope` reuse otomatis karena query lewat Model yang sama). | Ketua RT hanya menerima data RT-nya sendiri di response (403/empty, bukan bocor data RT lain). |
| FR API.4 | Surat Menyurat | List/detail/generate surat, download PDF via endpoint yang mengembalikan file (`application/pdf`) atau signed URL sementara. | Bendahara mendapat `403` (paritas FR01.3). PDF yang di-download identik dengan hasil generate di web. |
| FR API.5 | Keuangan (Kas) | CRUD kas masuk/keluar dengan upload bukti (`multipart/form-data`), laporan + export. | Sekretaris & Ketua RT mendapat `403` (paritas dengan middleware `role:super_admin,ketua_rw,bendahara` di web). |
| FR API.6 | Pengaduan | Warga submit pengaduan (+ foto opsional) dan lihat status miliknya sendiri; RT/RW update status. | Warga hanya bisa melihat/mengubah pengaduan miliknya sendiri (`403` untuk milik warga lain), status mengikuti alur FR05.2 PRD. |
| FR API.7 | Pengumuman | List publik tanpa token (mengganti kebutuhan endpoint publik terpisah dari web), CRUD untuk RW. | Endpoint publik hanya menampilkan pengumuman yang `publish_date <= now()` dan belum kedaluwarsa (paritas FR06). |
| FR API.8 | Inventaris | Paritas penuh dengan [Issue #001](001-modul-inventaris.md) FR08: kategori, barang, peminjaman, pengembalian, laporan. | Validasi stok tersedia & isolasi RT sama persis dengan versi web (reuse `InventoryLoan`/`InventoryItem` model, bukan logika baru). |
| FR API.9 | WhatsApp Broadcast & Template | List/kirim broadcast, CRUD template — hanya Super Admin/Ketua RW. | `403` untuk peran lain, konsisten `routes/web.php`. |
| FR API.10 | Dashboard Ringkasan | Satu endpoint agregat statistik (kartu, tren kas, piramida, aktivitas) sesuai visibilitas per peran. | Field yang tidak boleh dilihat peran tertentu (mis. saldo kas untuk Sekretaris/Ketua RT) bernilai `null`, bukan dihapus dari struktur JSON (memudahkan klien mobile parsing tipe tetap). |

## 5. Peran & Hak Akses

Tidak ada perubahan matriks peran — API **mewarisi** middleware `role:` yang sama persis dengan `routes/web.php` (lihat PRD Bagian 1.3 & tabel di [Issue #001](001-modul-inventaris.md) Bagian 5 untuk Inventaris). Middleware `EnsureUserHasRole` tidak spesifik ke guard `web`, sehingga bisa dipakai ulang langsung untuk guard `sanctum` tanpa modifikasi.

| Peran | Hak Akses via API |
| :--- | :--- |
| **Super Admin** | Akses penuh, identik web. |
| **Ketua RW** | Akses penuh lintas RT, identik web. |
| **Sekretaris** | Master Data, Surat, Inventaris (kategori & barang) — sama seperti web; tidak ada akses Keuangan. |
| **Bendahara** | Keuangan penuh; tidak ada akses Master Data warga, Surat, atau Inventaris. |
| **Ketua RT** | Data & Pengaduan wilayahnya sendiri, Inventaris miliknya — isolasi via global scope yang sama dengan web. |
| **Warga** | Login, lihat pengumuman, submit/lacak pengaduan miliknya, kelola "Data Saya", lihat status peminjaman barang jika sedang meminjam (opsional, sama seperti catatan terbuka di Issue #001 Bagian 9). |

## 6. Desain Teknis (Usulan)

Mengikuti konvensi Laravel Boost: **Eloquent API Resources** + **versioning eksplisit di path** (bukan header), konsisten dengan pola `role:` middleware dan struktur folder `app/Http/Controllers` yang sudah ada.

```
routes/api.php                          (baru — didaftarkan di bootstrap/app.php withRouting(api: ...))
app/Http/Controllers/Api/V1/*           (baru — controller API, terpisah dari controller Inertia)
app/Http/Resources/*                    (baru — satu Resource per entity yang diekspos)
config/sanctum.php                      (baru — hasil `php artisan install:api` atau publish manual)
database/migrations/..._personal_access_tokens_table.php  (baru — migrasi bawaan Sanctum)
```

Perubahan pada file existing:
- `app/Models/User.php` — tambah trait `Laravel\Sanctum\HasApiTokens`.
- `bootstrap/app.php` — tambah `api: __DIR__.'/../routes/api.php'` pada `withRouting()`, middleware group `api` default Sanctum (`auth:sanctum`).

**Response envelope** (konsisten di semua endpoint, memudahkan parsing klien mobile):

```json
// Sukses
{ "data": { ... }, "meta": { "current_page": 1, "last_page": 5, "per_page": 15, "total": 68 } }

// Error validasi (422, format bawaan Laravel — tidak perlu dibungkus ulang)
{ "message": "The email field is required.", "errors": { "email": ["The email field is required."] } }

// Error otorisasi/lainnya
{ "message": "Anda tidak memiliki akses ke halaman ini." }
```

`meta` hanya muncul pada endpoint list (paginated); endpoint detail/CRUD tunggal cukup `{ "data": {...} }`.

## 7. Alur Autentikasi

```
POST /api/v1/login {email, password}
  → 200 { data: { token, user: {...} } }         (kredensial valid & is_active = true)
  → 422 { message, errors }                       (kredensial tidak valid)

Authorization: Bearer <token>  (header di setiap request berikutnya)

POST /api/v1/logout
  → 200 { message: "Berhasil keluar." }           (revoke token yang sedang dipakai, bukan semua token milik user)
```

## 8. Kriteria Penerimaan Akhir (Definition of Done)

1. `php artisan route:list --path=api` menampilkan seluruh endpoint pada Bagian 4, masing-masing dengan middleware `auth:sanctum` + `role:` yang sesuai.
2. Login mengembalikan token yang valid dipakai untuk request berikutnya; token yang di-revoke (logout) langsung ditolak (`401`) pada request selanjutnya.
3. Setiap modul CMS di Bagian 3.1 punya minimal satu Feature Test yang memverifikasi: (a) akses sukses untuk peran yang berhak, (b) `403` untuk peran yang tidak berhak, (c) isolasi data Ketua RT tetap berlaku via API.
4. Response mengikuti envelope Bagian 6 secara konsisten di seluruh endpoint (diverifikasi lewat test, bukan review manual).
5. Dashboard web (Inertia) tidak mengalami regresi — `php artisan test --compact` untuk suite existing tetap hijau tanpa perubahan pada controller Inertia yang sudah ada.
6. `vendor/bin/pint --dirty --format agent` bersih untuk seluruh file baru.

## 9. Pertanyaan Terbuka

- Apakah endpoint publik (Pengumuman) perlu rate limit terpisah yang lebih longgar dibanding endpoint ber-token, mengingat berpotensi diakses tanpa autentikasi dalam volume lebih besar? (ya di ratelimit)
- Apakah upload foto (Pengaduan, Kas, Inventaris) di v1 cukup `multipart/form-data` biasa seperti web, atau aplikasi mobile butuh alur upload terpisah (presigned URL) agar tidak memblokir UI saat kirim data — diasumsikan cukup `multipart/form-data` biasa dulu, dioptimasi jika terbukti jadi masalah performa nyata.
- Perlu ditentukan siapa yang generate token pertama kali untuk warga yang belum pernah login (self-service "lupa password" via API, atau tetap reset manual oleh Sekretaris seperti alur web saat ini)? (ya tetap oleh sekretaris)
