# 12 — Fitur & Penjelasan Menu SENTRA

## A. Dashboard Keuangan
- Hero + filter periode & organisasi (developer), chip jam real-time.
- **Nyata dari database**: saldo, pemasukan, pengeluaran (Kas), total anggaran (RAB per periode), grafik arus kas bulanan (Chart.js).
- Aksi cepat ke semua menu + kartu Pengajuan removed (menu dihapus) + Iuran.

## B. Kas Organisasi — kas nyata
- CRUD pemasukan/pengeluaran: keterangan, jenis, tanggal, nominal (decimal 15,2), kategori (Sponsorship, Iuran, Operasional…).
- **BUKTI WAjib** (foto/PDF ≤10 MB): upload file atau **📷 Kamera** (getUserMedia → foto → input file). Tanpa bukti → error, tidak tersimpan.
- Saldo = pemasukan − pengeluaran; format Rupiah `Rp1.000.000`; pencarian judul/kategori; pagination.
- Bukti tampil thumbnail klik-untuk-lihat; ganti bukti lewat Edit; hapus data ikut hapus file.

## C. Anggaran & RAB
- CRUD RAB per proker + tahun: nama proker, **taut ke proker E-Progress (opsional)**, tahun, anggaran, realisasi awal.
- Persentase realisasi otomatis (`realized/allocated`), sisa anggaran, status (Belum Dipakai/Berjalan/Hampir Habis/Habis).
- Total anggaran per periode tampil di dashboard SENTRA.

## D. Iuran Anggota
- Catat iuran per pengurus: **dropdown nama dari Data Anggota**, nominal, periode, status, tanggal bayar, **BUKTI wajib**.
- **Tandai Lunas** → otomatis tercatat pemasukan Kas "Iuran {nama}" kategori Iuran Anggota (bukti tersalin); tidak dobel.
- Progres pembayaran (X/Y anggota, persen, terkumpul); filter status/periode; Edit/Hapus.

## E. Realisasi Anggaran (+ LPJ E-Progress)
- Grafik bar Anggaran vs Realisasi per proker; statistik total/sisa/persen.
- Tabel rincian: anggaran, realisasi, persen, **bukti LPJ dari E-Progress** (badge hijau → taut ke menu Evaluasi & LPJ), aksi Catat.
- **Catat Realisasi**: nominal + keterangan + tanggal + **BUKTI wajib** → pengeluaran Kas + `budgets.realized += nominal`.
- Daftar semua dokumen LPJ (`evaluations` tipe lpj) beserta kegiatan, penulis, status.

## F. Laporan Keuangan
- Laporan periodik per tahun; unduh CSV (pemasukan/pengeluaran/saldo per bulan).
- (Inventaris & peminjaman aset: tabel `inventories`/`asset_loans` tersedia untuk tahap lanjut.)

## Aturan uang (wajib)
- Semua transaksi uang (Kas, Iuran, Realisasi) **wajib lampir bukti** — foto via kamera HP (📷) atau upload struk/kuitansi.
- Iuran lunas & realisasi selalu menyalin bukti ke transaksi kas yang dibuatnya → jejak bukti utuh.

## Izin & Test
- Developer view-only lintas organisasi; organization tulis org-nya + tidak bisa mengakses RAB/transaksi org lain (403).
- Test: CashTransaction, Budget, Dues, Realisasi (±17 test SENTRA).
