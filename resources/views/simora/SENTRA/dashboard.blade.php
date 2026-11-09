@extends('simora.SENTRA.layout', ['title' => 'Dashboard Keuangan'])

@php
    $summary = $summary ?? [
        'totalKas' => 0, 'totalPemasukan' => 0, 'totalPengeluaran' => 0, 'totalAnggaran' => 0,
        'iuranLunas' => 0, 'iuranTotal' => 0,
    ];
    $uang = fn ($v) => 'Rp' . number_format((float) $v, 0, ',', '.');
    $saldo = (float) $summary['totalPemasukan'] - (float) $summary['totalPengeluaran'];
    $laporanParams = '?periode=' . $period . (($isDeveloper ?? false) && $selectedOrganization ? '&organization=' . $selectedOrganization->slug : '');
@endphp

@section('content')
    {{-- ===== Hero ===== --}}
    <section class="sentra-hero" aria-label="Ringkasan dashboard SENTRA">
        <div class="sentra-hero-decor" aria-hidden="true"><i class="d1"></i><i class="d2"></i><i class="d3"></i></div>
        <div class="sentra-hero-main">
            <p class="orgmate-kicker sentra-hero-kicker"><span class="sentra-hero-dot"></span> Dashboard SENTRA</p>
            <h1>Dashboard Keuangan</h1>
            <p class="sentra-hero-desc">Pusat kendali keuangan organisasi mahasiswa
                @if($isDeveloper ?? false) — mode developer, semua organisasi @endif.</p>
            <div class="sentra-hero-meta">
                <span class="sentra-chip"><i data-lucide="building-2"></i>{{ $orgName ?? 'Semua Organisasi' }}</span>
                <span class="sentra-chip"><i data-lucide="calendar-days"></i>Periode {{ $period }}</span>
                <span class="sentra-chip sentra-chip--live"><i></i>Terakhir diperbarui: {{ $todayLabel }}</span>
                <span class="sentra-chip sentra-chip--live"><i data-lucide="clock"></i><span data-live-clock>—</span></span>
            </div>
            <div class="sentra-hero-actions">
                <a class="sentra-btn sentra-btn--primary" href="{{ route('simora.sentra.panduan') }}"><i data-lucide="book-open"></i> Panduan SENTRA</a>
                <a class="sentra-btn sentra-btn--ghost" href="{{ route('simora.sentra.laporan') }}{{ $laporanParams }}"><i data-lucide="download"></i> Unduh Laporan</a>
            </div>
        </div>
        <form method="GET" action="{{ route('simora.sentra') }}" class="sentra-hero-filter" aria-label="Filter dashboard">
            <strong><i data-lucide="sliders-horizontal"></i> Filter Tampilan</strong>
            @if(($isDeveloper ?? false) && ($organizationsList ?? collect())->count() > 0)
                <label for="sentraOrgSelect">Organisasi</label>
                <select id="sentraOrgSelect" name="organization" onchange="this.form.submit()">
                    <option value="">Semua Organisasi</option>
                    @foreach ($organizationsList as $org)
                        <option value="{{ $org->slug }}" {{ $selectedOrganization?->slug === $org->slug ? 'selected' : '' }}>{{ $org->name }}</option>
                    @endforeach
                </select>
            @endif
            <label for="periode">Periode</label>
            <select id="periode" name="periode" onchange="this.form.submit()">
                @foreach (['2026', '2025', '2024'] as $y)
                    <option value="{{ $y }}" {{ $period === $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </form>
    </section>

    {{-- ===== 4 kartu statistik ===== --}}
    <div class="sentra-stats">
        <article class="sentra-stat sentra-stat--blue">
            <div class="sentra-stat-top">
                <span class="sentra-stat-icon"><i data-lucide="wallet"></i></span>
                <span class="sentra-stat-delta"><i data-lucide="trending-up"></i>Saldo</span>
            </div>
            <span class="sentra-stat-label">Total Kas</span>
            <strong>{{ $uang($summary['totalKas']) }}</strong>
            <small>Saldo kas organisasi saat ini</small>
        </article>
        <article class="sentra-stat sentra-stat--green">
            <div class="sentra-stat-top">
                <span class="sentra-stat-icon"><i data-lucide="arrow-up-circle"></i></span>
                <span class="sentra-stat-delta"><i data-lucide="trending-up"></i>Masuk</span>
            </div>
            <span class="sentra-stat-label">Total Pemasukan</span>
            <strong>{{ $uang($summary['totalPemasukan']) }}</strong>
            <small>Dari berbagai sumber dana</small>
        </article>
        <article class="sentra-stat sentra-stat--red">
            <div class="sentra-stat-top">
                <span class="sentra-stat-icon"><i data-lucide="arrow-down-circle"></i></span>
                <span class="sentra-stat-delta"><i data-lucide="trending-down"></i>Keluar</span>
            </div>
            <span class="sentra-stat-label">Total Pengeluaran</span>
            <strong>{{ $uang($summary['totalPengeluaran']) }}</strong>
            <small>Untuk kegiatan organisasi</small>
        </article>
        <article class="sentra-stat sentra-stat--purple">
            <div class="sentra-stat-top">
                <span class="sentra-stat-icon"><i data-lucide="calculator"></i></span>
                <span class="sentra-stat-delta"><i data-lucide="chart-pie"></i>RAB</span>
            </div>
            <span class="sentra-stat-label">Total Anggaran RAB</span>
            <strong>{{ $uang($summary['totalAnggaran']) }}</strong>
            <small>Total anggaran program kerja</small>
        </article>
    </div>

    {{-- ===== Aksi cepat ===== --}}
    <nav class="sentra-quick" aria-label="Akses cepat SENTRA">
        <a href="{{ route('simora.sentra.kas') }}"><span class="q-icon q-blue"><i data-lucide="wallet"></i></span><span><strong>Kas Organisasi</strong><small>Arus uang masuk &amp; keluar</small></span><i data-lucide="arrow-up-right" class="q-arrow"></i></a>
        <a href="{{ route('simora.sentra.rab') }}"><span class="q-icon q-purple"><i data-lucide="clipboard-list"></i></span><span><strong>Anggaran &amp; RAB</strong><small>Rencana anggaran proker</small></span><i data-lucide="arrow-up-right" class="q-arrow"></i></a>
        <a href="{{ route('simora.sentra.iuran') }}"><span class="q-icon q-green"><i data-lucide="users"></i></span><span><strong>Iuran Anggota</strong><small>Tagihan &amp; pelunasan</small></span><i data-lucide="arrow-up-right" class="q-arrow"></i></a>
        <a href="{{ route('simora.sentra.realisasi') }}"><span class="q-icon q-teal"><i data-lucide="receipt-text"></i></span><span><strong>Realisasi Anggaran</strong><small>Serapan dana proker</small></span><i data-lucide="arrow-up-right" class="q-arrow"></i></a>
        <a href="{{ route('simora.sentra.laporan') }}"><span class="q-icon q-red"><i data-lucide="file-text"></i></span><span><strong>Laporan Keuangan</strong><small>Ekspor PDF / Excel</small></span><i data-lucide="arrow-up-right" class="q-arrow"></i></a>
    </nav>

    {{-- ===== Arus kas + penggunaan anggaran ===== --}}
    <div class="sentra-row-2">
        <section class="orgmate-card sentra-card sentra-card--chart">
            <div class="orgmate-card-head">
                <h2><i data-lucide="bar-chart-3"></i> Statistik Arus Kas</h2>
                <div class="sentra-legend">
                    <span><i style="background:#2563EB"></i>Pemasukan</span>
                    <span><i style="background:#F59E0B"></i>Pengeluaran</span>
                    <span class="badge gray">Tahun {{ $period }}</span>
                </div>
            </div>
            <div class="sentra-empty-state" id="arusKasEmpty">
                <span class="sentra-empty-icon sentra-empty-icon--blue"><i data-lucide="chart-column"></i></span>
                <strong>Belum ada transaksi kas</strong>
                <p>Grafik akan terisi otomatis setelah ada transaksi pemasukan atau pengeluaran.</p>
                <a class="sentra-btn sentra-btn--primary sentra-btn--sm" href="{{ route('simora.sentra.kas') }}"><i data-lucide="plus"></i> Catat Transaksi Pertama</a>
            </div>
            <div class="sentra-chart-box" id="arusKasChartBox" style="display:none"><canvas id="chartArusKas" aria-label="Grafik arus kas"></canvas></div>
        </section>

        <section class="orgmate-card sentra-card">
            <div class="orgmate-card-head">
                <h2><i data-lucide="pie-chart"></i> Penggunaan Anggaran</h2>
                <a href="{{ route('simora.sentra.realisasi') }}">Lihat Detail <i data-lucide="arrow-right"></i></a>
            </div>
            <div class="sentra-donut-wrap">
                <div class="sentra-donut" role="img" aria-label="Serapan anggaran 0 persen">
                    <svg viewBox="0 0 120 120" aria-hidden="true">
                        <circle class="donut-bg" cx="60" cy="60" r="52"></circle>
                        <circle class="donut-val" cx="60" cy="60" r="52" style="stroke-dasharray:0 327"></circle>
                    </svg>
                    <div class="sentra-donut-center"><strong>0%</strong><small>terserap</small></div>
                </div>
                <div class="sentra-donut-legend">
                    <span><i style="background:#2563EB"></i>Terealisasi</span>
                    <span><i style="background:#E2E8F0"></i>Sisa anggaran</span>
                    <p class="orgmate-hint">Belum ada anggaran program kerja. Tambahkan RAB untuk mulai memantau penggunaan anggaran.</p>
                    <a class="sentra-btn sentra-btn--ghost sentra-btn--sm" href="{{ route('simora.sentra.rab') }}"><i data-lucide="clipboard-list"></i> Buat RAB</a>
                </div>
            </div>
        </section>
    </div>

    {{-- ===== Iuran ===== --}}
    <div class="sentra-row-2b">
        <section class="orgmate-card sentra-card">
            <div class="orgmate-card-head">
                <h2><i data-lucide="users"></i> Iuran Anggota</h2>
                <a href="{{ route('simora.sentra.iuran') }}">Lihat Detail <i data-lucide="arrow-right"></i></a>
            </div>
            <div class="sentra-iuran-wrap">
                <div class="sentra-ring" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" aria-label="Persentase iuran lunas 0 persen">
                    <svg viewBox="0 0 120 120" aria-hidden="true">
                        <circle class="ring-bg" cx="60" cy="60" r="52"></circle>
                        <circle class="ring-val" cx="60" cy="60" r="52" style="stroke-dasharray:0 327"></circle>
                    </svg>
                    <div class="sentra-ring-center"><strong>0%</strong><small>lunas</small></div>
                </div>
                <div class="sentra-iuran-meta">
                    <div class="sentra-iuran-count">{{ $summary['iuranLunas'] }} / {{ $summary['iuranTotal'] }} Anggota</div>
                    <div class="sentra-iuran-unpaid"><i data-lucide="circle-alert"></i> Belum ada data pembayaran</div>
                    <p class="orgmate-hint">Persentase anggota yang sudah melunasi iuran periode ini.</p>
                    <a class="sentra-btn sentra-btn--ghost sentra-btn--sm" href="{{ route('simora.sentra.iuran') }}"><i data-lucide="users"></i> Kelola Iuran</a>
                </div>
            </div>
        </section>
    </div>

    {{-- ===== Transaksi + Laporan ===== --}}
    <div class="sentra-row-3">
        <section class="orgmate-card sentra-card">
            <div class="orgmate-card-head">
                <h2><i data-lucide="list"></i> Transaksi Terbaru</h2>
                <select class="sentra-filter-select" aria-label="Filter transaksi" onchange="if(this.value) location.href=this.value">
                    <option value="">Semua Transaksi</option>
                    <option value="{{ route('simora.sentra.kas') }}">Lihat Kas Organisasi</option>
                </select>
            </div>
            <div class="sentra-empty-state sentra-empty-state--compact">
                <span class="sentra-empty-icon sentra-empty-icon--green"><i data-lucide="arrow-left-right"></i></span>
                <strong>Belum ada transaksi terbaru</strong>
                <p>Transaksi kas yang dicatat akan tampil di sini.</p>
            </div>
        </section>

        <section class="orgmate-card sentra-card sentra-laporan-card">
            <div class="orgmate-card-head">
                <h2><i data-lucide="file-bar-chart"></i> Laporan Keuangan</h2>
                <div class="sentra-laporan-tags">
                    <span class="sentra-tag-pdf"><i data-lucide="file-text"></i> PDF</span>
                    <span class="sentra-tag-excel"><i data-lucide="sheet"></i> Excel</span>
                </div>
            </div>
            <div class="sentra-laporan-illu" aria-hidden="true">
                <span class="sentra-laporan-blob"></span>
                <i data-lucide="trending-up"></i>
            </div>
            <strong class="sentra-laporan-title">Laporan Keuangan Tahun {{ $period }}</strong>
            <p class="orgmate-hint">Ekspor laporan keuangan dalam format PDF atau Excel.</p>
            <div class="sentra-laporan-actions">
                <a class="sentra-btn-download" href="{{ route('simora.sentra.laporan') }}{{ $laporanParams }}"><i data-lucide="file-text"></i> PDF</a>
                <a class="sentra-btn-download sentra-btn-download--green" href="{{ route('simora.sentra.laporan') }}{{ $laporanParams }}&format=excel"><i data-lucide="sheet"></i> Excel</a>
            </div>
        </section>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            // Grafik arus kas: tampil hanya jika sudah ada data transaksi
            var bulan = @json($bulan ?? []);
            var pemasukan = @json($pemasukan ?? []);
            var pengeluaran = @json($pengeluaran ?? []);
            var hasData = (pemasukan || []).concat(pengeluaran || []).some(function (v) { return Number(v) > 0; });
            var empty = document.getElementById('arusKasEmpty');
            var box = document.getElementById('arusKasChartBox');
            var canvas = document.getElementById('chartArusKas');
            if (hasData && window.Chart && canvas) {
                if (empty) empty.style.display = 'none';
                if (box) box.style.display = 'block';
                new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: bulan,
                        datasets: [
                            { label: 'Pemasukan', data: pemasukan, backgroundColor: '#2563EB', borderRadius: 6, barPercentage: 0.55, categoryPercentage: 0.6 },
                            { label: 'Pengeluaran', data: pengeluaran, backgroundColor: '#F59E0B', borderRadius: 6, barPercentage: 0.55, categoryPercentage: 0.6 }
                        ]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip: { callbacks: { label: function (c) { return ' ' + c.dataset.label + ': Rp' + Number(c.raw).toLocaleString('id-ID'); } } } },
                        scales: {
                            x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#64748b' } },
                            y: { border: { display: false }, grid: { color: '#eef2f7' }, ticks: { font: { size: 10 }, color: '#94a3b8', callback: function (v) { return 'Rp' + (v >= 1000 ? (v / 1000) + 'rb' : v); } } }
                        }
                    }
                });
            }
            if (window.lucide) window.lucide.createIcons();
        })();
    </script>
@endsection
