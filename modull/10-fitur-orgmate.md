# 10 — Fitur & Penjelasan Menu ORGMATE

## A. Dashboard Organisasi
- Hero sambutan waktu-based (pagi/siang/malam), statistik nyata dari database (total anggota/pengurus, organisasi terbaru, kegiatan terbaru, saldo kas, iuran lunas).
- Logo organisasi = foto profil akun organization; tampil di semua host.
- Tombol Panduan ORGMATE + jam real-time.

## B. Struktur Kepengurusan
- CRUD jabatan: nama jabatan, kelompok (Pimpinan / Wakil Pimpinan / BPI / Divisi), urutan tampil.
- Setiap jabatan menunjuk **satu pemegang** dari tabel `members` (satu jabatan satu pemegang).
- Dipakai landing `/struktur/{slug}`: bagan 4 tingkat dari `group_type` + anggota divisi sebagai kartu di bawah koordinatornya (koordinator tidak termasuk daftar anggota divisi).

## C. Data Anggota
- CRUD mahasiswa: nama, **NIM unik per organisasi**, email, telepon, fakultas, **divisi**, status aktif/nonaktif.
- Upload foto anggota (disk public, URL versi mtime).
- Modal edit + pagination khusus (`vendor.pagination.orgmate`) dengan info "Menampilkan X–Y dari Z anggota" dan `?per_page=5–50`.
- Anggota adalah **data keanggotaan, bukan akun login** — dipakai sebagai pemegang jabatan, saran nama panitia, dan dropdown iuran.

## D. Arsip SK & Surat
- CRUD arsip: judul, kategori (`sk/surat_masuk/surat_keluar`), nomor, tanggal, file (PDF/gambar/dokumen ≤10 MB).
- Kartu hitungan per kategori; file bisa dibuka/diunduh; file lama dihapus saat diganti/dihapus.

## E. Pengumuman
- CRUD pengumuman: judul + isi; terbaru muncul di dashboard organisasi.

## F. Rekrutmen Anggota
- Menu halaman pengelolaan rekrutmen; link pendaftaran organisasi yang disimpan akun organisasi tampil di card Open Recruitment landing.

## Pola akses ORGMATE
- Developer: view-only + pemilih organisasi `?organization=slug`.
- Organization: hanya boleh menulis data organisasinya sendiri (403 untuk data asing).

## Hubungan ke modul lain
- `members` → E-Progress Kepanitiaan (saran nama), SENTRA Iuran (dropdown pengurus), struktur landing.
- `positions.group_type` → bagan landing.
