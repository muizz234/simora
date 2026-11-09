# 01 — Arsitektur & Struktur Project

## Ringkasan
SIMORA (Sistem Manajemen Organisasi Kemahasiswaan) adalah aplikasi **monolit Laravel 13 / PHP 8.4** untuk mengelola organisasi kemahasiswaan Fakultas Teknik UNUJA. Satu aplikasi menyatukan tiga modul (ORGMATE, E-PROGRESS, SENTRA), Portal SSO, dan landing publik. Bukan microservice: semua domain berbagi satu database, satu auth session, dan satu OrganizationScope.

## Arsitektur logis

```
┌─────────────────────────────────────────────────────────────────┐
│                        Browser (UI)                              │
│  Landing publik │ Panel Utama │ ORGMATE │ E-PROGRESS │ SENTRA   │
└──────────────────────────────┬──────────────────────────────────┘
                               │ HTTP (blade + form POST/PUT/DELETE)
┌──────────────────────────────▼──────────────────────────────────┐
│                 Laravel Monolith (Controller + View)              │
│                                                                   │
│  Tiap menu dirender server-side (SSR Blade):                      │
│  Controller (validasi, scope organisasi) → Query Eloquent         │
│    → Blade view (OCI: layout + section + komponen)                │
│                                                                   │
│  Lapisan pendukung:                                               │
│   • app/Support/OrganizationScope — penegak kepemilikan data      │
│   • Middleware: auth, role, ForceRootUrlFromRequest, signed       │
│   • domain_events (outbox) — sinkronisasi antar modul             │
│   • API publik read-only /api/v1 + api_token                      │
└──────────────────────────────┬──────────────────────────────────┘
                               │ MySQL (24+ tabel)
┌──────────────────────────────▼──────────────────────────────────┐
│  Relasional: users ──< organizations ──< tabel modul             │
└─────────────────────────────────────────────────────────────────┘
```

## Alur request (pola baku)

```
HTTP request
  → Middleware web (session, CSRF, ForceRootUrlFromRequest)
  → auth (session login SSO) + role:developer,organization
  → Controller
      1. organizationContext(): developer boleh pilih ?organization=,
         organization selalu terikat organization_id login
      2. Query dibatasi organization_id (scope organisasi)
      3. Validasi input (Form Request inline / $request->validate)
      4. Simpan/ubah/hapus + flash session 'status'
      5. Redirect kembali ke halaman menu (PRG pattern)
  → Blade view
      layout.blade.php modul (sidebar, topbar, breadcrumb)
      + halaman (intro, statistik pastel, tabel, form, dialog edit)
      + CSS/JS versi ?v= untuk cache-busting
```

## Struktur folder penting

```
SIMORA/
├── app/
│   ├── Http/
│   │   ├── Controllers/        ← 30 controller (lihat 05)
│   │   ├── Middleware/         ← auth, role, ForceRootUrlFromRequest
│   ├── Models/                 ← 22 model Eloquent (lihat 04)
│   ├── Support/                ← OrganizationScope
│   ├── Observers/              ← DomainEvent outbox
│   └── Console/                ← job/komando jadwal
├── database/
│   ├── migrations/             ← 24 migrasi (lihat 06)
│   └── seeders/, factories/
├── resources/
│   ├── views/
│   │   ├── welcome.blade.php            ← landing publik
│   │   ├── struktur/, kegiatan/        ← halaman publik
│   │   ├── partials/, components/      ← header, footer, kartu org
│   │   └── simora/
│   │       ├── ORGMATE/   (dashboard, struktur, anggota, arsip, pengumuman, rekrutmen, panduan)
│   │       ├── E-PROGRESS/(dashboard, proker, kalender, kepanitiaan, presensi, dokumentasi, evaluasi, monitoring, panduan)
│   │       ├── SENTRA/    (dashboard, kas, rab, iuran, realisasi, laporan, panduan)
│   │       ├── panel.blade.php, organization-panel.blade.php
│   │       └── organisasi/, akun/, layouts
│   ├── css/                    ← sumber CSS per modul (kasus sama di public/)
│   └── js/                     ← landing.js, clock.js
├── public/
│   ├── assets/css|js/          ← hasil sinkron CSS/JS (?v=)
│   ├── storage/                ← bukti/foto via disk public
│   └── build/                  ← Vite (Vite manifest)
├── routes/ web.php, api.php, console.php, bootstrap/app.php
├── tests/Feature/              ← ±40 test fitur per menu
└── modull/                     ← dokumentasi project ini
```

## Keputusan arsitektur utama

1. **Satu monolit, tiga modul logis** — pemisahan hanya di folder/routes/view, bukan service.
2. **Kepemilikan data = `organization_id` di semua tabel modul**. `OrganizationScope::assertCanAccessOrganization` mencegah akses lintas organisasi (403).
3. **Role ganda**: `developer` = view + pemilih organisasi lintas-org; `organization` = tulis data hanya org-nya (konsisten di semua modul).
4. **Anggota ≠ akun login** — `members` adalah data keanggotaan mahasiswa; `users` adalah akun login SSO.
5. **Sinkronisasi antar modul** via tabel `domain_events` (outbox) yang ditulis Observer Organization/User.
6. **Akses publik**: landing read-only dari database; file bukti/foto di disk `public` dengan URL relatif dipaksa host request (`ForceRootUrlFromRequest` agar localhost & ngrok tampil logo yang sama).
7. **Jam real-time** satu file `public/assets/js/clock.js` dipakai landing, panel, dashboard.

## Diagram flowchart arsitektur

```mermaid
flowchart LR
    B[Browser] -->|GET/POST/PUT/DELETE| MW[Middleware<br/>web, auth, role, signed, root-url]
    MW --> C[Controller<br/>context organisasi + validasi]
    C --> Q[Query Eloquent<br/>scope organization_id]
    Q --> DB[(MySQL)]
    DB --> Q
    Q --> V[Blade view<br/>layout modul + halaman]
    V --> B
    C -. domain_events .-> OB[Observer outbox]
    OB -.-> DB
