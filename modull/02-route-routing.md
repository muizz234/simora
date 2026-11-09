# 02 — Routing

## File route
| File | Isi |
|---|---|
| `routes/web.php` | Semua route browser: landing publik, auth, panel, grup `/simora` (modül), presensi publik bertanda tangan |
| `routes/api.php` | API publik read-only `GET /api/v1/...` + `GET /api/v1/me` (token auth) |
| `routes/console.php` | Jadwal & perintah konsol |
| `bootstrap/app.php` | Alias middleware: `role`, `api.token`; pengecualian CSRF |

## Grup middleware
Route modul di dalam grup:
```php
Route::middleware(['auth', 'role:developer,organization'])
    ->prefix('simora')->name('simora.')
    ->group(function () { ... });
```
Tulis data (POST/PUT/DELETE) ditambah `->middleware('role:organization')` sehingga developer hanya dapat GET (view-only).

## Peta route publik

| Method | URI | Nama | Fungsi |
|---|---|---|---|
| GET | `/` | `home` | Landing publik |
| GET | `/kontak` | `kontak` | Halaman kontak |
| GET | `/struktur` | `struktur.index` | Daftar organisasi untuk struktural |
| GET | `/struktur/{organization:slug}` | `struktur.show` | Bagan struktural organisasi |
| GET | `/kegiatan` | `kegiatan.index` | Daftar kegiatan + dokumentasi |
| GET | `/kegiatan/{activity}` | `kegiatan.show` | Detail kegiatan + galeri |
| GET | `/login`, POST `/login`, POST `/logout` | `login`, `login.store`, `logout` | SSO session |
| GET | `/panel` | `panel` | Panel utama (developer/organisasi) |
| GET | `/presensi/{activity}` (POST sama) | `presensi.checkin(.store)` | Check-in publik QR (middleware `signed`) |

## Peta route ORGMATE (`simora.orgmate.*`)

| Route | Method | URI | Fungsi |
|---|---|---|---|
| `orgmate` | GET | `/simora/orgmate` | Dashboard organisasi |
| `orgmate.panduan` | GET | `/simora/orgmate/panduan` | Panduan ORGMATE |
| `orgmate.struktur` | GET | `/simora/orgmate/struktur` | Struktur kepengurusan |
| `orgmate.anggota` (+`.store/.update/.destroy`) | GET/POST/PUT/DELETE | `/simora/orgmate/anggota[/{member}]` | Data anggota |
| `orgmate.rekrutmen` | GET | `/simora/orgmate/rekrutmen` | Rekrutmen |
| `orgmate.arsip` (+`.store/.update/.destroy`) | GET/POST/PUT/DELETE | `/simora/orgmate/arsip[/{archive}]` | Arsip SK & surat |
| `orgmate.pengumuman` (+`.store/.update/.destroy`) | GET/POST/PUT/DELETE | `/simora/orgmate/pengumuman[/{announcement}]` | Pengumuman |

## Peta route E-PROGRESS (`simora.e-progress.*`)

| Route | Method | URI | Fungsi |
|---|---|---|---|
| `e-progress` | GET | `/simora/e-progress` | Dashboard kegiatan |
| `e-progress.panduan` | GET | `/simora/e-progress/panduan` | Panduan E-Progress |
| `e-progress.proker` (+store/update/destroy) | GET/POST/PUT/DELETE | `/simora/e-progress/proker[/{workProgram}]` | Program kerja |
| `e-progress.kalender` (+store/update/destroy) | GET/POST/PUT/DELETE | `/simora/e-progress/kalender[/{activity}]` | Kalender + kegiatan |
| `e-progress.kepanitiaan` (+store/update/destroy) | GET/POST/PUT/DELETE | `/simora/e-progress/kepanitiaan[/{committee}]` | Susunan panitia |
| `e-progress.presensi` (+store/destroy) | GET/POST/DELETE | `/simora/e-progress/presensi[/{attendance}]` | QR presensi + daftar |
| `e-progress.dokumentasi` (+store/update/destroy) | GET/POST/PUT/DELETE | `/simora/e-progress/dokumentasi[/{documentation}]` | Galeri dokumentasi |
| `e-progress.evaluasi` (+store/update/destroy) | GET/POST/PUT/DELETE | `/simora/e-progress/evaluasi[/{evaluation}]` | Evaluasi & LPJ |
| `e-progress.monitoring` | GET | `/simora/e-progress/monitoring` | Progres proker |

## Peta route SENTRA (`simora.sentra.*`)

| Route | Method | URI | Fungsi |
|---|---|---|---|
| `sentra` | GET | `/simora/sentra` | Dashboard keuangan |
| `sentra.panduan` | GET | `/simora/sentra/panduan` | Panduan SENTRA |
| `sentra.kas` (+store/update/destroy) | GET/POST/PUT/DELETE | `/simora/sentra/kas[/{cashTransaction}]` | Kas organisasi |
| `sentra.rab` (+store/update/destroy) | GET/POST/PUT/DELETE | `/simora/sentra/rab[/{budget}]` | Anggaran & RAB |
| `sentra.iuran` (+store/update/destroy/markPaid) | GET/POST/PUT/DELETE | `/simora/sentra/iuran[/{due}]`, `/{due}/lunas` | Iuran anggota |
| `sentra.realisasi` (+store) | GET/POST | `/simora/sentra/realisasi` | Realisasi anggaran (kas+RAB+LPJ) |
| `sentra.laporan` | GET | `/simora/sentra/laporan` | Laporan keuangan (CSV) |

## Route lain
- `simora.organizations.*`, `simora.accounts.*` — kelola organisasi & akun (developer)
- `simora.my-registration.update/destroy`, `my-socials.update`, `my-vision-mission.update` — profil organisasi
- `simora.landing-management(.update)` — kelola landing (developer)
- `simora.kalender-kerja` — kalender kerja organisasi

## Parameter & pola
- Model binding implisit Laravel (`{member}`, `{activity}`, `{due}`, …) + query string `?organization=slug`, `?activity=id`, `?periode=YYYY`, `?q=`, `?status=`, `?type=`, `?year=&month=`.
- Presensi publik memakai URL bertanda tangan (`URL::signedRoute`) + middleware `signed`; tanpa signature → 403.
- Penamaan: `simora.{modul}.{menu}[.store|.update|.destroy]`.

## Diagram route flow

```mermaid
flowchart LR
    PUB[Landing publik<br/>/ , /struktur, /kegiatan, /kontak] --> GET[Controller publik]
    PUB2[QR scan peserta] --> SIGN[GET /presensi/{activity}?signature]
    SIGN -->|valid| CHECKIN[Form check-in nama]
    SIGN -->|tak valid| F403[403 Forbidden]
    LOGIN[Login SSO] --> PANEL[/panel]
    PANEL --> ORG[Panel organisasi] --> ORGMATE[/simora/orgmate...]
    PANEL --> EPROG[/simora/e-progress...]
    PANEL --> SENTRA[/simora/sentra...]
    ORGMATE -->|tulis| POST[POST/PUT/DELETE role:organization]
    POST -->|scope org| DB[(MySQL)]
