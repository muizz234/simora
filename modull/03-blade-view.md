# 03 — Blade & View

## Sistem templating
- Semua tampilan adalah **Blade server-side** (SSR). Tidak ada SPA/JS framework.
- Setiap modul punya **satu layout** yang menentukan sidebar, topbar, breadcrumb, toast, dan script global:
  - `simora/ORGMATE/layout.blade.php`
  - `simora/E-PROGRESS/layout.blade.php`
  - `simora/SENTRA/layout.blade.php`
- Halaman meng-`@extends` layout modul + `@section('content')`.

## Struktur view

```
resources/views/
├── welcome.blade.php                ← landing publik (beranda)
├── kontak.blade.php                 ← halaman kontak
├── struktur/
│   ├── index.blade.php              ← daftar organisasi → struktural
│   └── show.blade.php               ← bagan struktural bertingkat (4 tingkat)
├── kegiatan/
│   ├── index.blade.php              ← daftar kartu kegiatan + dokumentasi
│   └── show.blade.php               ← detail kegiatan + galeri
├── auth/                            ← form login
├── components/
│   ├── organization-card.blade.php
│   └── panel-header.blade.php
├── partials/
│   ├── site-header.blade.php
│   ├── site-footer.blade.php
│   └── camera-capture.blade.php     ← dialog ambil foto kamera (SENTRA)
├── vendor/pagination/orgmate.blade.php
└── simora/
    ├── panel.blade.php / organization-panel.blade.php
    ├── ORGMATE/*.blade.php
    ├── E-PROGRESS/*.blade.php
    └── SENTRA/*.blade.php
```

## Pola halaman modul (standar, wajib diikuti)
1. **Intro**: kicker + judul + lead + aksi (pemilih organisasi, tombol kembali, tombol Panduan).
2. **Toast status**: `@if (session('status'))` dengan kelas `orgmate-toast is-show`.
3. **Statistik pastel**: `.eprogress-stat` / `.sentra-stat` + ikon Lucide + warna pastel (biru/hijau/ungu/amber/merah).
4. **Kartu** `.orgmate-card`: head (judul + badge jumlah) → isi (tabel/galeri/list) → pagination.
5. **Form**: `.orgmate-form` + `.orgmate-form-grid` + `.orgmate-field`; error per field `@error('...')`.
6. **Edit modal**: `<dialog class="orgmate-dialog">` + JS buka/tutup (klik backdrop menutup).
7. **Ikon**: Lucide (`data-lucide`) — ikon `users` diganti `users` (bukan `users` kuno). Jam real-time: `<span data-live-clock>` + `clock.js`.

## CSS (sumber → publik, wajib sinkron)
| Sumber | Publik | Dipakai oleh |
|---|---|---|
| `resources/css/simora/orgmate/orgmate.css` | `public/assets/css/orgmate.css` | ORGMATE, E-Progress, SENTRA (layout bersama) |
| `resources/css/simora/e-progress/e-progress.css` | `public/assets/css/e-progress.css` | E-Progress |
| `resources/css/simora/sentra/sentra.css` | `public/assets/css/sentra.css` | SENTRA |
| `resources/css/landing/landing.css` | `public/assets/css/landing.css` | Landing, struktur, kegiatan |
| `resources/css/struktur/struktur.css` | `public/assets/css/struktur.css` | Halaman struktural |

- Naikkan `?v=` di `<link>` setiap mengubah CSS agar browser me-refresh.
- Jam dibagikan: `public/assets/js/clock.js` (WIB, tiap 1 detik) di landing, panel, dashboard modul.
- Kamera: `resources/views/partials/camera-capture.blade.php` dipakai Kas/Iuran/Realisasi.

## Komponen & partial
- `x-organization-card` — kartu organisasi di landing (foto pengurus, logo, visi-misi, link struktural).
- `x-panel-header` — header panel utama.
- `partials.site-footer` — footer semua halaman publik (sinkron: navbar footer termasuk Kegiatan).
- `partials.camera-capture` — dialog `<video>` + JS `getUserMedia` → File → input bukti (DataTransfer).

## Tema
- Satu kunci tema: `localStorage ft-theme` (`body.dark-mode`).
- Panel & modul memakai navy `#092e69` + biru `#1265d8` selaras landing; dark mode via variabel `--sentra-*` dan override `.dark-mode`.
- Semua layout responsif: breakpoint 1024/768/640/480/360 px.

## Diagram render view

```mermaid
flowchart LR
    LAYOUT[Layout modul<br/>sidebar + topbar + breadcrumb] -->|yield content| PAGE[Halaman modul<br/>intro + statistik + kartu]
    PAGE --> CARD[Card: tabel / galeri / list]
    PAGE --> FORM[Form + dialog edit]
    FORM -->|POST/PUT/DELETE| CTRL[Controller]
    CTRL -->|flash status| PAGE
    PARTIAL[Partials<br/>footer, camera dialog] --> LAYOUT
    CSS[CSS ?v= + clock.js] --> LAYOUT
