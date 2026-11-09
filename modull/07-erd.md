# 07 — ERD (Entity Relationship Diagram)

## ERD utama (inti + ORGMATE)

```mermaid
erDiagram
    users {
        bigint id
        string name
        string email
        string password
        string role
        bigint organization_id
        string nim
        string avatar_path
        string api_token
    }
    organizations {
        bigint id
        string name
        string slug
        string full_name
        string focus
        string period
        string logo_path
    }
    members {
        bigint id
        bigint organization_id
        string name
        string nim
        string division
        string status
    }
    positions {
        bigint id
        bigint organization_id
        bigint member_id
        string name
        string group_type
    }
    users ||--o{ organizations : "memiliki"
    organizations ||--o{ members : "anggota"
    organizations ||--o{ positions : "jabatan"
    members ||--o{ positions : "pemegang"
```

## ERD E-PROGRESS

```mermaid
erDiagram
    work_programs {
        bigint id
        bigint organization_id
        string name
        string status
        date start_date
        date end_date
    }
    activities {
        bigint id
        bigint organization_id
        bigint work_program_id
        string name
        datetime start_at
        string status
    }
    committees {
        bigint id
        bigint activity_id
        string name
        string role
    }
    attendances {
        bigint id
        bigint activity_id
        string name
        timestamp checked_at
        string method
    }
    documentations {
        bigint id
        bigint activity_id
        string title
        string type
        string file_path
    }
    evaluations {
        bigint id
        bigint activity_id
        string title
        string type
        string status
    }
    work_programs ||--o{ activities : "1 proker, banyak kegiatan"
    activities ||--o{ committees : "panitia"
    activities ||--o{ attendances : "presensi"
    activities ||--o{ documentations : "galeri"
    activities ||--o{ evaluations : "evaluasi/LPJ"
```

## ERD SENTRA + integrasi

```mermaid
erDiagram
    budgets {
        bigint id
        bigint work_program_id
        string program_name
        smallint year
        decimal allocated
        decimal realized
    }
    cash_transactions {
        bigint id
        string type
        string title
        decimal amount
        date date
        string proof_path
    }
    dues {
        bigint id
        string name
        decimal amount
        string status
        timestamp paid_at
        string proof_path
    }
    inventories {
        bigint id
        string name
        string category
    }
    asset_loans {
        bigint id
        bigint inventory_id
        string borrower_name
        date loan_date
    }
    budgets ||--o{ cash_transactions : "realisasi mencatat pengeluaran"
    budgets }o--|| evaluations : "LPJ sebagai bukti"
    dues ||--o{ cash_transactions : "lunas -> pemasukan"
    inventories ||--o{ asset_loans : "dipinjam"
```

## Keterangan relasi
- Semua FK organisasi bersifat `cascadeOnDelete`; FK ke pengguna (`created_by`, `uploaded_by`, `user_id`) bersifat `nullOnDelete` agar data tidak hilang bila akun dihapus.
- `budgets.work_program_id` adalah jembatan SENTRA ↔ E-Progress; `evaluations` tipe LPJ adalah jembatan bukti LPJ → Realisasi SENTRA.
- `dues.name` menyimpan nama pengurus (dari `members`); `dues.user_id` opsional (pemilik tagihan) — karena pengurus bukan akun login.
