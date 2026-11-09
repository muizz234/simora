# 11 — Fitur & Penjelasan Menu E-PROGRESS

## A. Dashboard Kegiatan
- Statistik nyata: total kegiatan, proker, peserta terlibat (presensi), kegiatan selesai, menunggu persetujuan (versi lama — kini sesuai data).
- Grafik 6 bulan terakhir (kegiatan vs selesai), kegiatan terbaru, banner + periode kepengurusan + tombol Panduan E-PROGRESS + jam real-time.

## B. Proker (Program Kerja)
- CRUD: nama, deskripsi, **status tahapan** (perencanaan→pelaksanaan→evaluasi→pelaporan→selesai), tanggal mulai/selesai.
- Proker adalah jangkar integrasi SENTRA (RAB) dan progres monitoring.

## C. Kalender Kegiatan
- Kalender bulanan interaktif: grid 7×6, header Sen–Min, navigasi bulan, penanda hari ini, kegiatan tampil chip di tanggal mulai.
- Daftar "Kegiatan Bulan Ini" (status, proker, jadwal) + CRUD kegiatan: nama, proker, lokasi, mulai/selesai (`datetime-local`), status, deskripsi. Edit via modal, hapus dengan konfirmasi.
- Developer: monitoring + pemilih organisasi.

## D. Kepanitiaan
- Per kegiatan: susun panitia (nama + peran: Ketua Pelaksana, Koordinator Acara, Sekretaris, Bendahara, Sie, dll.). Nama disarankan dari Data Anggota ORGMATE.
- Statistik: total kegiatan, kegiatan berpanitia, total panitia. CRUD edit/hapus.

## E. Absensi / Presensi (QR Code)
- QR asli (SVG, ±260 px) per kegiatan mengarah ke URL check-in **bertanda tangan** (tanpa login).
- Alur: tayangkan QR → peserta scan → form nama → "Saya Hadir" → tercatat `method=qr`.
- Tombol Unduh QR (SVG) & Cetak (hanya kartu QR yang tercetak).
- Daftar hadir: nomor, avatar inisial, badge metode (QR/Manual), pencarian nama. Catat manual oleh panitia + hapus.

## F. Dokumentasi Kegiatan
- Upload **multi-berkas** (maks 10/files, ≤20 MB): foto, video, dokumen, lainnya + judul & jenis.
- Galeri grid foto (klik → ukuran penuh) + daftar video/dokumen (Buka/Unduh/Edit/Hapus).
- Dokumen tertaut ke landing publik `/kegiatan` (kartu + galeri) dan detail `/kegiatan/{id}` (meta lengkap + foto semua + kegiatan lain org sama).

## G. Evaluasi & LPJ Kegiatan
- Per kegiatan: tulis dokumen bertipe **Evaluasi** atau **LPJ**, status Draft/Selesai, isi teks.
- Filter tab Semua/Evaluasi/LPJ. Dokumen LPJ tertaut otomatis ke SENTRA Realisasi sebagai bukti pertanggungjawaban.

## H. Monitoring Progres Proker
- Progres **nyata** per proker: kegiatan selesai ÷ total kegiatan (bar + persen), rincian tahapan, kegiatan terakhir.
- Statistik: total proker, rata-rata progres, proker 100%, proker belum ada kegiatan.

## Izin & Test
- Developer view-only lintas organisasi; organization tulis org-nya (kegiatan/panitia/dokumentasi terikat proker milik sendiri).
- Test: WorkProgram, ActivityCalendar, Committee, Attendance, Documentation, Evaluation, MonitoringProgress (±26 test).
