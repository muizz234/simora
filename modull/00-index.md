# Dokumentasi SIMORA (Folder `modull/`)

Satu file per aspek penjelasan project. Mulai dari arsitektur, lalu route → blade → model → controller → database → diagram → fitur per modul → auth.

| # | File | Isi |
|---|---|---|
| 01 | [01-arsitektur-struktur-project.md](01-arsitektur-struktur-project.md) | Arsitektur monolit, struktur folder, alur request, keputusan utama |
| 02 | [02-route-routing.md](02-route-routing.md) | Peta semua route publik & modul + middleware |
| 03 | [03-blade-view.md](03-blade-view.md) | Struktur Blade, pola halaman, CSS/JS, komponen |
| 04 | [04-model-dan-relasi.md](04-model-dan-relasi.md) | 22 model + relasi + atribut khas |
| 05 | [05-controller.md](05-controller.md) | 30 controller + tanggung jawab + pola controller |
| 06 | [06-database-tabel.md](06-database-tabel.md) | Semua tabel + kolom + integrasi antar modul |
| 07 | [07-erd.md](07-erd.md) | ERD inti, E-Progress, SENTRA (Mermaid) |
| 08 | [08-dfd.md](08-dfd.md) | DFD level 0/1/2 (konteks, modul, aliran uang) |
| 09 | [09-flowchart-alur-fitur.md](09-flowchart-alur-fitur.md) | Flowchart alur login, ORGMATE, E-Progress, SENTRA |
| 10 | [10-fitur-orgmate.md](10-fitur-orgmate.md) | Penjelasan lengkap tiap menu ORGMATE |
| 11 | [11-fitur-eprogress.md](11-fitur-eprogress.md) | Penjelasan lengkap tiap menu E-Progress |
| 12 | [12-fitur-sentra.md](12-fitur-sentra.md) | Penjelasan lengkap tiap menu SENTRA |
| 13 | [13-auth-dan-role.md](13-auth-dan-role.md) | SSO session, role, OrganizationScope, keamanan |
| 14 | [14-database-schema-akses-dan-sinkronisasi.md](14-database-schema-akses-dan-sinkronisasi.md) | Schema DB + tabel yang diakses tiap halaman (landing, login, panel, ORGMATE, E-Progress, SENTRA) + alur sinkronisasi SSO & antar modul |
| 15 | [modulSIMORA.md](modulSIMORA.md) | **Dokumentasi lengkap & final project**: gabungan urut isi 01–14 (UI/UX, Blade/CSS/JS, fitur ORGMATE/E-PROGRESS/SENTRA, route/controller/model, database + ERD/DFD/flowchart, auth & sinkronisasi) + microservice readiness (contract/DTO/directory, internal API, health check, retry, outbox & ingest) + hardening P0/P1 (rotasi/expiry token, rate limit per token, circuit breaker, retry/backoff, dead-letter & replay, FK decoupling, event projection, Docker Compose, CI) + pengujian & panduan migrasi |
