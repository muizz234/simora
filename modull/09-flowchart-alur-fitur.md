# 09 — Flowchart Alur Fitur (per Menu)

## Alur login & panel

```mermaid
flowchart TD
    A[Daftar Organisasi/User] --> B[Login SSO]
    B --> C[Panel Utama]
    C --> D{Role}
    D -->|developer| E[Panel Developer: statistik semua org]
    D -->|organization| F[Panel Organisasi: statistik org sendiri]
    E --> G[Modal: ORGMATE / E-PROGRESS / SENTRA]
    F --> G
```

## ORGMATE — Struktur & Anggota

```mermaid
flowchart TD
    A[Buka Struktur Kepengurusan] --> B[Tambah Jabatan + kelompok]
    B --> C[Tunjuk pemegang anggota]
    C --> D[Bagan struktural landing terupdate]
    A2[Buka Data Anggota] --> E[Tambah anggota: nama, NIM unik, divisi, foto]
    E --> F{Data valid?}
    F -->|tidak| G[Error field]
    F -->|ya| H[Tersimpan + pagination]
    H --> I[Anggota dipakai sebagai pemegang jabatan & panitia & dropdown iuran]
```

## E-PROGRESS — Proker → Kalender → Kegiatan

```mermaid
flowchart TD
    A[Menu Proker] --> B[Simpan proker + status tahapan]
    B --> C[Menu Kalender Kegiatan]
    C --> D[Tambah kegiatan + proker terkait + jadwal]
    D --> E[Kegiatan tampil chip di kalender]
    E --> F[Kepanitiaan: tambah panitia]
    F --> G[Presensi: tampilkan QR]
    G --> H[Peserta scan QR -> isi nama -> tercatat]
    H --> I[Dokumentasi: upload multi foto]
    I --> J[Evaluasi/LPJ ditulis]
    J --> K[Monitoring: progres = kegiatan selesai / total]
    K --> L[Kegiatan terpublikasi di landing /kegiatan]
```

## E-PROGRESS — Presensi QR (publik)

```mermaid
flowchart TD
    A[Panitia tampilkan QR kegiatan] --> B[Peserta scan]
    B --> C[URL bertanda tangan]
    C --> {Signature valid?}
    C -->|tidak| E403[403 Forbidden]
    C -->|ya| F[Form nama peserta]
    F --> G[Tombol Saya Hadir]
    G --> H[Tercatat: method=qr, waktu sekarang]
    H --> I[Hal: Berhasil Check-in]
    H --> J[Daftar hadir di dashboard panitia update]
```

## SENTRA — Uang masuk/keluar

```mermaid
flowchart TD
    A[Anggaran & RAB: buat anggaran per proker+tahun] --> B[Kas: catat pemasukan/pengeluaran + BUKTI wajib]
    C[Iuran: catat + BUKTI] --> D{Tandai Lunas?}
    D -->|ya| E[Uang masuk kas otomatis + bukti tersalin]
    B --> F[Realisasi: catat pengeluaran + bukti]
    F --> G[Kas berkurang + RAB realized naik + grafik terisi]
    G --> H[Laporan: unduh CSV per tahun]
    A --> I[Dashboard: total anggaran tampil]
    B --> J[Dashboard: saldo, pemasukan, grafik bulanan]
```

## Diagram flowchart umum fitur

```mermaid
flowchart LR
    U[User] --> LOGIN[Login]
    LOGIN --> DASH[Dashboard modul]
    DASH --> LIST[Daftar data]
    LIST --> FORM[Form tambah]
    FORM --> VAL{Validasi + bukti?}
    VAL -->|gagal| FORM
    VAL -->|lolos| SAVE[Simpan DB + file]
    SAVE --> LIST
    LIST --> EDIT[Edit modal]
    EDIT --> SAVE
    LIST --> DEL[Hapus + konfirmasi]
    DEL --> LIST
