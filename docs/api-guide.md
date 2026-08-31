# Panduan REST API SIM-RW (v1)

**Status:** Implementasi awal selesai — lihat [`docs/issues/002-rest-api.md`](issues/002-rest-api.md) untuk latar belakang, keputusan desain, dan kriteria penerimaan.

Dokumen ini adalah panduan praktis untuk developer yang mengonsumsi API (mis. aplikasi mobile warga). Untuk detail teknis lengkap tiap endpoint (skema request/response, tipe field), gunakan dokumentasi interaktif yang dibuat otomatis dari kode sumber (lihat [Dokumentasi Interaktif](#dokumentasi-interaktif) di bawah) — dokumen ini tidak mengulang detail tersebut agar tidak basi ketika kode berubah.

---

## 1. Dasar

- **Base URL:** `https://<domain-instalasi-anda>/api/v1`
- **Format:** JSON untuk semua request/response (kecuali upload file yang memakai `multipart/form-data`, dan unduh PDF/Excel yang mengembalikan file biner).
- **Autentikasi:** Bearer token (Laravel Sanctum personal access token). **Tidak ada endpoint publik tanpa token** — bahkan endpoint yang sifatnya "lihat saja" seperti daftar pengumuman tetap mewajibkan token, agar setiap akses API bisa diaudit ke satu akun pengguna.
- **RBAC:** Persis sama dengan dashboard web (`routes/web.php`) — peran yang tidak boleh mengakses suatu menu di web juga akan mendapat `403` di API yang setara.

## 2. Alur Autentikasi

### 2.1. Login

```
POST /api/v1/login
Content-Type: application/json

{ "email": "warga@example.com", "password": "rahasia" }
```

Respons sukses (`200`):

```json
{
  "data": {
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "user": { "id": 1, "name": "...", "email": "...", "role": "warga", "resident": {} }
  }
}
```

Kredensial salah atau akun `is_active = false` → `422` dengan `errors.email`. Percobaan login dibatasi **5 kali per menit** per kombinasi email+IP (terpisah dari rate limit umum di bawah).

### 2.2. Memakai token

Sertakan token di header `Authorization` pada setiap request berikutnya:

```
Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

Token **tidak kedaluwarsa otomatis** (belum ada kebijakan expiry di v1 — lihat Pertanyaan Terbuka di issue #002). Simpan secara aman di sisi klien (mis. Keychain/Keystore pada aplikasi mobile, bukan `localStorage` biasa).

### 2.3. Logout

```
POST /api/v1/logout
Authorization: Bearer <token>
```

Mencabut token yang sedang dipakai (bukan seluruh token milik akun tersebut — pengguna bisa login dari beberapa perangkat).

## 3. Format Respons

| Situasi | Bentuk |
| :--- | :--- |
| Sukses (item tunggal) | `{ "data": { ... } }` |
| Sukses (daftar, dipaginasi) | `{ "data": [...], "links": {...}, "meta": { "current_page", "last_page", "per_page", "total" } }` |
| Validasi gagal (`422`) | `{ "message": "...", "errors": { "field": ["pesan..."] } }` |
| Tidak berwenang (`403`) / token tidak valid (`401`) | `{ "message": "..." }` |

## 4. Rate Limiting

- Endpoint umum: mengikuti limiter `api` bawaan Laravel (**60 request/menit** per token/IP).
- `POST /login`: **5 request/menit** per kombinasi email+IP (mencegah brute force).

## 5. Ringkasan Modul

Lihat tabel lengkap peran & endpoint per modul di halaman **Panduan API** pada dashboard (menu khusus Super Admin, `/panduan-api`), atau di [`docs/issues/002-rest-api.md`](issues/002-rest-api.md) Bagian 4. Ringkasan cepat:

| Modul | Prefix | Peran yang boleh akses |
| :--- | :--- | :--- |
| Autentikasi | `/login`, `/logout`, `/user` | Semua |
| Dashboard & Data Saya | `/dashboard`, `/data-saya` | Semua (field tertentu dibatasi per peran) |
| Wilayah & Profil RW | `/wilayah/*`, `/rw`, `/rt` | Super Admin, Ketua RW |
| KK & Penduduk | `/family-heads`, `/residents*` | Super Admin, Ketua RW, Sekretaris, Ketua RT (terisolasi per RT) |
| Surat Menyurat | `/letter-templates`, `/letters*` | Super Admin, Ketua RW, Sekretaris |
| Keuangan | `/treasury-categories`, `/treasuries`, `/treasury-report*` | Super Admin, Ketua RW, Bendahara |
| Pengaduan | `/complaints*` | Super Admin, Ketua RW, Ketua RT, Warga |
| Pengumuman | `/announcements` | Semua melihat; Super Admin/Ketua RW mengelola |
| Inventaris | `/inventory-*` | Super Admin, Ketua RW, Sekretaris, Ketua RT (terisolasi per RT) |
| WhatsApp | `/whatsapp-*` | Super Admin, Ketua RW |

## 6. Dokumentasi Interaktif

Dokumentasi lengkap (skema request/response, coba-langsung dari browser) dibuat otomatis oleh [Scramble](https://scramble.dedoc.co/) dari kode sumber (route, Form Request, API Resource) — selalu sinkron dengan endpoint yang benar-benar berjalan, tidak perlu ditulis ulang manual setiap ada perubahan.

- **UI interaktif:** `/docs/api`
- **Spesifikasi OpenAPI (JSON):** `/docs/api.json`

Keduanya **hanya bisa diakses oleh akun Super Admin** (di luar environment `local`) — diatur lewat `Gate::define('viewApiDocs', ...)` di `AppServiceProvider` dan middleware `RestrictedDocsAccess` bawaan Scramble (`config/scramble.php`). Di dashboard, menu **Panduan API** (khusus Super Admin) menyediakan tautan langsung ke keduanya beserta ringkasan alur autentikasi.

Perintah Artisan terkait:

```bash
php artisan scramble:analyze   # cek ada tidaknya error saat menganalisis kode untuk dokumentasi
php artisan scramble:export    # ekspor spesifikasi OpenAPI ke file api.json (mis. untuk import ke Postman)
php artisan scramble:cache     # cache dokumen OpenAPI yang sudah digenerate
php artisan scramble:clear     # bersihkan cache tsb.
```

## 7. Menambah Endpoint Baru

1. Tambahkan route di `routes/api.php` di dalam grup `auth:sanctum` (dan `role:` yang sesuai) — **jangan** buat route API tanpa `auth:sanctum` kecuali benar-benar setara dengan `/login` (menerbitkan token).
2. Buat controller di `app/Http/Controllers/Api/V1/`, reuse `FormRequest` & Model yang sama dengan controller web bila modulnya sudah ada di web, agar aturan bisnis tidak terduplikasi.
3. Buat/reuse `App\Http\Resources\*Resource` untuk bentuk response — jangan `return $model` mentah.
4. Jalankan `php artisan scramble:analyze` untuk memastikan dokumentasi tetap bisa digenerate tanpa error.
5. Tambahkan Feature Test di `tests/Feature/Api/V1/` yang memverifikasi akses per peran (termasuk isolasi RT bila relevan).
