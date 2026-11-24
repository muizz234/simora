# SIMORA

Sistem informasi organisasi mahasiswa berbasis Laravel 13. SIMORA menyatukan tiga modul dalam satu aplikasi:

| Modul | Fokus utama |
|---|---|
| **ORGMATE** | Struktur kepengurusan, anggota, arsip SK/surat, pengumuman, rekrutmen |
| **E-PROGRESS** | Program kerja, kalender kegiatan, kepanitiaan, presensi QR, dokumentasi, evaluasi/LPJ |
| **SENTRA** | Kas, anggaran/RAB, iuran, realisasi anggaran, laporan keuangan |

Saat ini aplikasi masih berupa **monolit yang terstruktur untuk dipisah menjadi microservice**. Kontrak baca antar-modul, internal API, health check, dan outbox event sudah ada; lihat [status microservice](#status-microservice).

## Daftar isi

- [Kebutuhan](#kebutuhan)
- [Instalasi lokal](#instalasi-lokal)
- [Menjalankan aplikasi](#menjalankan-aplikasi)
- [Pengujian](#pengujian)
- [Docker](#docker)
- [Konfigurasi penting](#konfigurasi-penting)
- [Status microservice](#status-microservice)
- [API](#api)
- [Outbox & event](#outbox--event)
- [Struktur project](#struktur-project)
- [Dokumentasi](#dokumentasi)
- [CI/CD](#cicd)

## Kebutuhan

- PHP 8.4 (ekstensi `bcmath`, `gd`, `intl`, `mbstring`, `pdo_mysql`, `zip`)
- Composer 2
- MySQL 8
- Node.js 22 + npm
- Opsional: Docker Desktop + Docker Compose

## Instalasi lokal

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
```

Isi koneksi database di `.env`, lalu:

```bash
php artisan migrate --seed
npm run build
php artisan storage:link
```

## Menjalankan aplikasi

Jalankan tiga proses terpisah di terminal berbeda:

```bash
php artisan serve
npm run dev
php artisan queue:work
```

Relay outbox dijalankan otomatis tiap menit oleh scheduler. Untuk menjalankan scheduler lokal:

```bash
php artisan schedule:work
```

Aplikasi tersedia di `http://localhost:8000`.

## Pengujian

```bash
vendor/bin/pint --dirty --format agent   # perbaiki gaya kode
php artisan test --compact               # seluruh suite
php artisan test --compact --filter=EventIngestTest
```

Suite memakai SQLite in-memory sehingga tidak memengaruhi database lokal.

## Docker

```bash
cp .env.example .env
# isi APP_KEY, DB_PASSWORD, dan MICROSERVICE_TOKEN minimal

docker compose up -d --build
docker compose exec app php artisan migrate --force
docker compose exec app php artisan storage:link
```

Aplikasi: `http://localhost:8080`. MySQL berjalan di kontainer `mysql` (port.host dapat diubah lewat `MYSQL_PORT`).

Layanan Compose:

| Layanan | Fungsi |
|---|---|
| `app` | PHP-FPM + kode aplikasi |
| `nginx` | Web server, forward `.php` ke `app` |
| `mysql` | Database |
| `queue` | Worker antrean (`queue:work`) |
| `scheduler` | Penjadwal (`schedule:work`), termasuk relay outbox |

Perintah berguna:

```bash
docker compose logs -f app nginx queue scheduler
docker compose exec app php artisan test --compact
docker compose down
docker compose down -v   # hapus volume DB — hati-hati
```

## Konfigurasi penting

Semua variabel ada di `.env.example`. Yang paling sering diubah:

| Variabel | Arti | Default |
|---|---|---|
| `MICROSERVICE_MODE` | `local` (satu DB) atau `http` (internal API) | `local` |
| `MICROSERVICE_TOKEN` | Token untuk `/api/internal/*` | kosong |
| `MICROSERVICE_TOKEN_EXPIRES_AT` | Tenggat token utama (ISO-8601) | kosong |
| `MICROSERVICE_TOKEN_PREVIOUS` | Token lama saat rotasi | kosong |
| `MICROSERVICE_TOKEN_PREVIOUS_EXPIRES_AT` | Tenggat token lama | kosong |
| `MICROSERVICE_RATE_LIMIT_PER_MINUTE` | Kuota internal API per token | `120` |
| `MICROSERVICE_EVENT_TRANSPORT` | `log` atau `http` | `log` |
| `MICROSERVICE_EVENT_WEBHOOK` | URL penerima event saat transport `http` | kosong |
| `MICROSERVICE_CIRCUIT_FAILURE_THRESHOLD` | Gagal berturut sebelum circuit OPEN | `5` |
| `MICROSERVICE_CIRCUIT_COOLDOWN_SECONDS` | Masa tunggu sebelum probe | `60` |
| `MICROSERVICE_OUTBOX_MAX_ATTEMPTS` | Batas percobaan sebelum dead-letter | `5` |
| `MICROSERVICE_OUTBOX_BACKOFF_SECONDS` | Jeda dasar backoff eksponensial | `30` |
| `ORGMATE_SERVICE_URL`, `EPROGRESS_SERVICE_URL`, `SENTRA_SERVICE_URL` | Base URL internal API tiap calon service | kosong |

> Jangan commit `.env` atau token produksi. Gunakan secret manager / environment variabel hosting.

## Status microservice

Yang **sudah** ada:

- Enam kontrak `Directory` + DTO `final readonly`; implementasi `local`/`http` dipilih lewat `MICROSERVICE_MODE`.
- Internal API `/api/internal/v1/*` dengan token, kuota per token, rotasi token, dan dukungan expiry.
- Health check `GET /api/health` (mengembalikan 503 jika database mati).
- Outbox `domain_events`, relay, retry dengan backoff, circuit breaker, dan dead-letter.
- Consumer event idempoten (`event_id`), anti-loop (`published_at`), dan projection ke `event_projections`.

Yang **belum** ada:

- Proses/service terpisah dan database terpisah (masih satu aplikasi + satu MySQL).
- Broker pesan sungguhan (transport `http` masih webhook).
- Metrik, tracing, dan health check mendalam (queue/scheduler/storage).

Rincian keputusan dan roadmap ada di [`modull/modulSIMORA.md`](modull/modulSIMORA.md) Bab VII–VIII.

### Rotasi token internal API

1. Pasang token baru di `MICROSERVICE_TOKEN`.
2. Pindahkan token lama ke `MICROSERVICE_TOKEN_PREVIOUS`.
3. Isi `MICROSERVICE_TOKEN_PREVIOUS_EXPIRES_AT` (mis. besok) dan `MICROSERVICE_TOKEN_EXPIRES_AT` untuk token baru.
4. Deploy ulang semua konsumen, lalu hapus token lama setelah tenggat.

## API

| Endpoint | Keterangan |
|---|---|
| `GET /api/health` | Status aplikasi + database (tanpa token) |
| `GET /api/v1/organizations` | Daftar organisasi (publik, read-only) |
| `GET /api/v1/organizations/{slug}` | Detail organisasi |
| `GET /api/v1/organizations/{slug}/structure` | Struktur organisasi |
| `GET /api/v1/me` | Profil pemilik token user |
| `GET /api/internal/v1/organizations/{id}/members` | Direktori anggota (service token) |
| `GET /api/internal/v1/organizations/{id}/work-programs` | Direktori program kerja |
| `GET /api/internal/v1/organizations/{id}/lpjs` | Direktori LPJ |
| `GET /api/internal/v1/organizations/{id}/budgets` | Direktori anggaran |
| `GET /api/internal/v1/organizations/{id}/dues` | Direktori iuran |
| `GET /api/internal/v1/organizations/{id}/cash-transactions` | Direktori transaksi kas |
| `POST /api/internal/v1/events` | Terima domain event |

Internal API memakai header `X-Service-Token: <token>` atau `Authorization: Bearer <token>`.

## Outbox & event

```bash
php artisan domain-events:relay                    # kirim event unpublished
php artisan domain-events:replay-dead-letter      # requeue event gagal permanen
php artisan schedule:work                          # menjalankan relay tiap menit
```

Alur: observer model → `domain_events` → `domain-events:relay` → webhook/log → `POST /api/internal/v1/events` → `event_projections`. Event yang diterima ditandai `published_at` agar tidak dikirim balik.

## Struktur project

```text
app/
├── Http/Controllers/      # controller web + API
├── Http/Middleware/       #_auth, role, service token
├── Models/                # Eloquent model
├── Modules/               # ORGMATE / E-PROGRESS / SENTRA + Shared
│   └── Shared/Events/     # publisher, projector, circuit breaker, katalog event
├── Observers/             # pencatat event domain
├── Providers/             # observasi model, binding directory, rate limiter
└── Support/               # OrganizationScope, DomainEventRecorder
database/migrations/       # schema + hardening outbox
modull/                    # dokumentasi lengkap project
resources/                 # Blade, CSS, JS sumber
routes/                    # web, api, console
docker/                    # konfigurasi nginx
```

## Dokumentasi

- [`modull/00-index.md`](modull/00-index.md) — indeks 15 file dokumentasi.
- [`modull/modulSIMORA.md`](modull/modulSIMORA.md) — referensi final: UI/UX, fitur, backend, database, ERD/DFD, auth, sinkronisasi, dan microservice readiness.

## CI/CD

GitHub Actions (`.github/workflows/ci.yml`) menjalankan, pada setiap push dan pull request:

1. `composer install` + `npm ci` + `npm run build`
2. Pint dalam mode cek (`--test`)
3. `php artisan test --compact`
4. `docker build` untuk memastikan image tetap bisa dibangun

Status deploy produksi belum otomatis; gunakan Docker image yang dihasilkan pipeline sebagai artefak.
