@extends('simora.SENTRA.layout', ['title' => 'Laporan Keuangan'])

@section('content')
    <div class="sentra-head">
        <div>
            <h1>Laporan Keuangan Periodik</h1>
            <p>Rekap arus kas dan realisasi anggaran untuk periode yang dipilih. Ekspor ke PDF atau Excel.</p>
        </div>
        @if(($isDeveloper ?? false) && ($organizationsList ?? collect())->count() > 0)
        <form method="GET" action="{{ route('simora.sentra.laporan') }}" class="sentra-periode">
            <i data-lucide="building-2"></i>
            <label for="laporanOrgSelect">Organisasi:</label>
            <select id="laporanOrgSelect" name="organization" onchange="this.form.submit()">
                <option value="">Semua Organisasi</option>
                @foreach($organizationsList as $org)
                    <option value="{{ $org->slug }}" {{ $selectedOrganization?->slug === $org->slug ? 'selected' : '' }}>{{ $org->name }}</option>
                @endforeach
            </select>
        </form>
    @endif
    <form method="GET" action="{{ route('simora.sentra.laporan') }}" class="sentra-periode">
            <i data-lucide="calendar-days"></i>
            <label>Periode:</label>
            <select name="periode" onchange="this.form.submit()">
                @foreach (['2026', '2025', '2024'] as $y)
                    <option value="{{ $y }}" {{ $periode === $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="orgmate-card" id="printArea">
        <div class="orgmate-card-head">
            <h2><i data-lucide="file-bar-chart"></i> Laporan Keuangan Tahun {{ $periode }} — {{ $orgName }}</h2>
            <div class="sentra-actions" style="margin:0">
                <a class="orgmate-btn ghost"
                    href="{{ route('simora.sentra.laporan') }}?periode={{ $periode }}&format=excel{{ ($isDeveloper ?? false) && $selectedOrganization ? '&organization='.$selectedOrganization->slug : '' }}"><i
                        data-lucide="sheet"></i> Excel</a>
                <button class="orgmate-btn" type="button" onclick="window.print()"><i data-lucide="file-text"></i> PDF
                    (Cetak)</button>
            </div>
        </div>

        <div class="orgmate-empty" style="margin-bottom:12px">Belum ada data keuangan untuk periode {{ $periode }}.
            Halaman laporan ini akan terisi setelah transaksi dan anggaran dicatat.</div>

        <h3 class="sentra-strong" style="margin:16px 0 8px;font-size:13px">Arus Kas Bulanan</h3>
        <div class="orgmate-table-wrap">
            <table class="orgmate-table">
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th style="text-align:right">Pemasukan</th>
                        <th style="text-align:right">Pengeluaran</th>
                        <th style="text-align:right">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bulan as $i => $b)
                        <tr>
                            <td><strong>{{ $b }} {{ $periode }}</strong></td>
                            <td class="sentra-table-num">Rp0</td>
                            <td class="sentra-table-num">Rp0</td>
                            <td class="sentra-table-num" style="font-weight:800">Rp0</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <h3 class="sentra-strong" style="margin:20px 0 8px;font-size:13px">Realisasi Anggaran per Proker</h3>
        <div class="orgmate-empty">Belum ada data anggaran.</div>

        <h3 class="sentra-strong" style="margin:20px 0 8px;font-size:13px">Transaksi Terbaru</h3>
        <div class="orgmate-empty">Belum ada data transaksi.</div>
        <p class="orgmate-hint">Untuk PDF: klik tombol "PDF (Cetak)" lalu pilih "Save as PDF" pada dialog cetak. Untuk
            Excel: klik tombol "Excel" untuk mengunduh file CSV yang dapat dibuka di Microsoft Excel.</p>
    </div>
@endsection
