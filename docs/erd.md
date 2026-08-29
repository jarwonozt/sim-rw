# Entity Relationship Diagram (ERD)
## Sistem Informasi Manajemen RW (SIM-RW)

Diagram ini menerjemahkan seluruh Functional Requirements pada [`prd.md`](./prd.md) (FR01–FR07) menjadi skema database. Nama tabel mengikuti konvensi Laravel (snake_case, plural) dan tabel inti (`master_rw`, `master_rt`, `residents`, `family_heads`, `letters`, `treasuries`, `complaints`) sesuai acuan di Bagian 11 PRD.

---

## Diagram

```mermaid
erDiagram
    PROVINCES ||--o{ REGENCIES : "membawahi"
    REGENCIES ||--o{ DISTRICTS : "membawahi"
    DISTRICTS ||--o{ VILLAGES : "membawahi"
    VILLAGES ||--o{ MASTER_RW : "membawahi"
    MASTER_RW ||--o{ MASTER_RT : "membawahi"
    MASTER_RT ||--o{ FAMILY_HEADS : "menaungi"
    FAMILY_HEADS ||--o{ RESIDENTS : "beranggotakan"

    USERS ||--o| RESIDENTS : "terhubung ke akun warga"
    MASTER_RW }o--|| USERS : "dipimpin oleh"
    MASTER_RT }o--|| USERS : "dipimpin oleh"

    LETTER_TEMPLATES ||--o{ LETTERS : "dipakai untuk"
    RESIDENTS ||--o{ LETTERS : "menerima"
    USERS ||--o{ LETTERS : "menerbitkan"

    TREASURY_CATEGORIES ||--o{ TREASURIES : "mengelompokkan"
    USERS ||--o{ TREASURIES : "mencatat"

    USERS ||--o{ COMPLAINTS : "mengajukan"
    MASTER_RT ||--o{ COMPLAINTS : "menaungi wilayah"
    COMPLAINTS ||--o{ COMPLAINT_LOGS : "mempunyai riwayat"
    USERS ||--o{ COMPLAINT_LOGS : "memperbarui status"

    USERS ||--o{ ANNOUNCEMENTS : "menerbitkan"

    MASTER_RT ||--o{ PATROL_SCHEDULES : "menjadwalkan"
    RESIDENTS ||--o{ PATROL_SCHEDULES : "bertugas sebagai petugas"

    USERS ||--o{ ACTIVITY_LOGS : "melakukan aksi"

    PROVINCES {
        bigint id PK
        string name
    }

    REGENCIES {
        bigint id PK
        bigint province_id FK
        string name
    }

    DISTRICTS {
        bigint id PK
        bigint regency_id FK
        string name
    }

    VILLAGES {
        bigint id PK
        bigint district_id FK
        string name
    }

    MASTER_RW {
        bigint id PK
        bigint village_id FK
        string nomor_rw
        bigint ketua_rw_id FK "nullable, -> users.id"
        string address
        timestamp created_at
        timestamp updated_at
    }

    MASTER_RT {
        bigint id PK
        bigint master_rw_id FK
        string nomor_rt
        bigint ketua_rt_id FK "nullable, -> users.id"
        timestamp created_at
        timestamp updated_at
    }

    USERS {
        bigint id PK
        string name
        string email UK
        string password
        enum role "super_admin|ketua_rw|sekretaris|bendahara|ketua_rt|warga"
        bigint resident_id FK "nullable"
        boolean is_active
        timestamp last_login_at
        timestamp created_at
        timestamp updated_at
    }

    FAMILY_HEADS {
        bigint id PK
        bigint rt_id FK "-> master_rt.id"
        string no_kk UK "16 digit"
        string address
        string postal_code
        timestamp created_at
        timestamp updated_at
    }

    RESIDENTS {
        bigint id PK
        bigint family_head_id FK
        string nik UK "16 digit"
        string name
        enum gender "L|P"
        string birth_place
        date birth_date
        string relationship_status "hubungan dlm KK"
        string occupation
        string religion
        string education
        string marital_status
        string phone
        string photo "nullable"
        timestamp created_at
        timestamp updated_at
    }

    LETTER_TEMPLATES {
        bigint id PK
        string name
        string type "domisili|sktm|usaha|dll"
        text content "blade template + placeholder"
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }

    LETTERS {
        bigint id PK
        string letter_number UK "nomor agenda surat"
        bigint letter_template_id FK
        bigint resident_id FK
        bigint issued_by FK "-> users.id"
        string purpose "tujuan surat"
        date issued_date
        string file_path "hasil PDF"
        timestamp created_at
        timestamp updated_at
    }

    TREASURY_CATEGORIES {
        bigint id PK
        string name
        enum type "in|out"
    }

    TREASURIES {
        bigint id PK
        bigint treasury_category_id FK
        enum type "in|out"
        decimal amount
        string description
        string proof_photo "bukti struk/foto"
        date transaction_date
        bigint created_by FK "-> users.id"
        timestamp created_at
        timestamp updated_at
    }

    COMPLAINTS {
        bigint id PK
        bigint user_id FK "pengadu"
        bigint rt_id FK "-> master_rt.id, untuk scoping"
        string title
        text description
        string photo "nullable"
        enum status "menunggu_verifikasi_rt|diteruskan_rw|proses|selesai"
        timestamp created_at
        timestamp updated_at
    }

    COMPLAINT_LOGS {
        bigint id PK
        bigint complaint_id FK
        string status
        string note "nullable"
        bigint changed_by FK "-> users.id"
        timestamp created_at
    }

    ANNOUNCEMENTS {
        bigint id PK
        string title
        text content
        string image "nullable"
        date publish_date
        date expire_date "nullable"
        bigint created_by FK "-> users.id"
        timestamp created_at
        timestamp updated_at
    }

    PATROL_SCHEDULES {
        bigint id PK
        bigint rt_id FK "-> master_rt.id"
        bigint resident_id FK "petugas ronda"
        date schedule_date
        string shift
        enum status "scheduled|done|missed"
        timestamp created_at
        timestamp updated_at
    }

    ACTIVITY_LOGS {
        bigint id PK
        bigint user_id FK
        string action
        string description
        string ip_address
        timestamp created_at
    }
```

---

## Catatan Desain

1. **Wilayah administratif** (`provinces` → `regencies` → `districts` → `villages` → `master_rw` → `master_rt`) mengikuti hierarki Kemendagri agar kompatibel dengan struktur data Dukcapil di masa depan (lihat Bagian 2.2 PRD — integrasi Dukcapil V2).
2. **`users.role`** dibuat sebagai `enum` tunggal, bukan tabel `roles`/`permissions` terpisah, selaras dengan filosofi *Boring Stack* (Bagian 5 PRD) — cukup untuk RBAC sederhana via middleware (FR01.3).
3. **Isolasi data per RT** (Bagian 6.2 PRD): `family_heads.rt_id` dan `complaints.rt_id` menjadi kunci penerapan *Global Scope* Eloquent agar Ketua RT hanya melihat data wilayahnya.
4. **`users.resident_id`** bersifat nullable — dipakai saat akun Warga (Viewer) ingin ditautkan ke data kependudukan miliknya sendiri (untuk fitur cek tagihan/status pengaduan pribadi).
5. **`complaint_logs`** memisahkan riwayat status dari tabel `complaints` agar histori tracking (FR05.2) tetap utuh meski status terus berubah.
6. **`activity_logs`** mendukung kebutuhan Audit Trail (Bagian 6.3 PRD) — mencatat siapa, kapan, dan aksi apa.
7. Modul **Jadwal Ronda** (FR07, opsional V1) sudah dimasukkan sebagai `patrol_schedules` agar skema tidak perlu migrasi besar saat fitur ini diaktifkan.

## Cara Membaca Diagram

Notasi mengikuti standar Crow's Foot pada Mermaid:
- `||--o{` : satu ke banyak (one-to-many), sisi `o{` boleh kosong (opsional/nullable FK).
- `||--o|` : satu ke nol-atau-satu (one-to-zero-or-one).
- `}o--||` : banyak ke satu (many-to-one, dibaca dari sisi kiri).
