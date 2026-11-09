@extends('simora.E-PROGRESS.layout', ['title' => 'Dashboard Kegiatan'])

@section('content')
    <div class="eprogress-new">

        {{-- Banner + kartu kanan --}}
        <div class="eprogress-banner-row">
            <section class="eprogress-banner">
                <div class="eprogress-banner-body">
                    <span class="eprogress-pill">Selamat Datang!</span>
                    <h1>Manajemen Kegiatan &amp; <span>Program Kerja</span></h1>
                    <p>Kelola, rencanakan, dan pantau seluruh kegiatan organisasi dengan lebih mudah dan terstruktur.</p>
                    <button class="eprogress-guide" type="button" onclick="window.location.href='{{ route('simora.e-progress.panduan') }}'">Panduan E-PROGRESS <i
                            data-lucide="arrow-right"></i></button>
                </div>
                <div class="eprogress-banner-art" aria-hidden="true"><i data-lucide="calendar-days" class="icon"></i></div>
            </section>
            <aside class="eprogress-sidecards">
                <div class="eprogress-sidecard">
                    <span class="eprogress-sidecard-label"><i data-lucide="calendar-days" class="icon"></i> Periode Kepengurusan</span>
                    <div class="eprogress-sidecard-value">{{ $periodLabel }}</div>
                </div>
                <div class="eprogress-sidecard">
                    <span class="eprogress-sidecard-label"><i data-lucide="clock" class="icon"></i> Jam Sekarang</span>
                    <div class="eprogress-sidecard-value" data-live-clock>—</div>
                </div>
                <div class="eprogress-sidecard">
                    <span class="eprogress-sidecard-label"><i data-lucide="users" class="icon"></i> Organisasi Aktif</span>
                    <div class="eprogress-sidecard-value">{{ $orgName ?? '—' }}</div>
                    <small
                        class="eprogress-sidecard-hint">{{ $isDeveloper ?? false ? 'Developer: mengelola semua organisasi' : 'Sesuai organisasi login' }}</small>
                    @if (($isDeveloper ?? false) && ($organizationsList ?? collect())->count() > 0)
                        <form method="GET" action="{{ route('simora.e-progress') }}" style="margin-top:8px">
                            <label class="eprogress-sidecard-label" for="eprogressOrgSelect">Lihat organisasi:</label>
                            <select id="eprogressOrgSelect" name="organization" onchange="this.form.submit()">
                                <option value="">Semua Organisasi</option>
                                @foreach ($organizationsList as $org)
                                    <option value="{{ $org->slug }}"
                                        {{ $selectedOrganization?->slug === $org->slug ? 'selected' : '' }}>
                                        {{ $org->name }}</option>
                                @endforeach
                            </select>
                        </form>
                    @endif
                </div>
            </aside>
        </div>

        {{-- 5 kartu pastel --}}
        <div class="eprogress-stats eprogress-stats-5">
            <article class="eprogress-stat eprogress-stat--pastel-blue"><span class="eprogress-stat-icon"><i
                        data-lucide="calendar-check"></i></span>
                <div><span>Total Kegiatan</span><strong>{{ $stats['totalKegiatan'] }}</strong><small>Seluruh periode</small>
                </div>
            </article>
            <article class="eprogress-stat eprogress-stat--pastel-green"><span class="eprogress-stat-icon"><i
                        data-lucide="clipboard-list"></i></span>
                <div><span>Program Kerja</span><strong>{{ $stats['totalProker'] }}</strong><small>Terdaftar proker</small></div>
            </article>
            <article class="eprogress-stat eprogress-stat--pastel-purple"><span class="eprogress-stat-icon"><i
                        data-lucide="users"></i></span>
                <div><span>Peserta Terlibat</span><strong>{{ number_format($stats['pesertaTerlibat']) }}</strong><small>Presensi tercatat</small></div>
            </article>
            <article class="eprogress-stat eprogress-stat--pastel-amber"><span class="eprogress-stat-icon"><i
                        data-lucide="check"></i></span>
                <div><span>Kegiatan Selesai</span><strong>{{ $stats['kegiatanSelesai'] }}</strong><small>Status selesai</small>
                </div>
            </article>
        </div>

        {{-- Statistik + Agenda + Aksi Cepat --}}
        <div class="eprogress-mid">
            <section class="orgmate-card eprogress-chart-card">
                <div class="orgmate-card-head">
                    <h2><i data-lucide="chart-column" class="icon"></i> Statistik Kegiatan</h2>
                    <div class="eprogress-tabs"><span class="badge gray">6 Bulan Terakhir</span><span
                            class="badge blue">Kegiatan</span><span class="badge gray">Peserta</span><span
                            class="badge gray">Progres</span></div>
                </div>
                <div class="orgmate-empty">Belum ada data kegiatan. Grafik akan terisi setelah program kerja dibuat.</div>
                <div class="orgmate-chart-box" style="display:none"><canvas id="chartKegiatan"></canvas></div>
            </section>
            <section class="orgmate-card">
                <div class="orgmate-card-head">
                    <h2><i data-lucide="calendar-check" class="icon"></i> Agenda Mendatang</h2><a
                        href="{{ route('simora.e-progress') }}">Lihat Semua →</a>
                </div>
                <div class="orgmate-empty">Belum ada agenda mendatang.</div>
            </section>
            <section class="orgmate-card">
                <div class="orgmate-card-head">
                    <h2><i data-lucide="zap" class="icon"></i> Aksi Cepat</h2><a href="{{ route('simora.e-progress') }}">Lihat Semua
                        →</a>
                </div>
                @if(($isDeveloper ?? false))
                    <div class="orgmate-empty">Developer mengelola kegiatan lintas organisasi (mode monitoring). Aksi operasional dilakukan oleh organisasi.</div>
                @else
                    <div class="eprogress-quick">
                        <a class="eprogress-quick-tile eprogress-tile--blue" href="{{ route('simora.e-progress') }}"><span><i
                                    data-lucide="calendar-plus"></i></span>Tambah Kegiatan</a>
                        <a class="eprogress-quick-tile eprogress-tile--green" href="{{ route('simora.e-progress') }}"><span><i
                                    data-lucide="users"></i></span>Bentuk Panitia</a>
                        <a class="eprogress-quick-tile eprogress-tile--amber" href="{{ route('simora.e-progress') }}"><span><i
                                    data-lucide="qr-code"></i></span>Generate QR Presensi</a>
                        <a class="eprogress-quick-tile eprogress-tile--indigo" href="{{ route('simora.e-progress') }}"><span><i
                                    data-lucide="image"></i></span>Upload Dokumentasi</a>
                        <a class="eprogress-quick-tile eprogress-tile--teal" href="{{ route('simora.e-progress') }}"><span><i
                                    data-lucide="file-text"></i></span>Buat LPJ</a>
                    </div>
                @endif
            </section>
        </div>

        {{-- Bawah: progres, kegiatan terbaru, notifikasi --}}
        <div class="eprogress-bottom">
            <section class="orgmate-card">
                <div class="orgmate-card-head">
                    <h2><i data-lucide="target" class="icon"></i> Progres Program Kerja</h2><a
                        href="{{ route('simora.e-progress') }}">Lihat Detail →</a>
                </div>
                <div class="eprogress-progress-wrap">
                    <div class="eprogress-ring"><span>0%</span><small>Total Proker Tah этом Ini</small></div>
                    <div class="eprogress-bars">
                        <div><span>Perencanaan</span>
                            <div class="orgmate-progress"><i style="width:0%;background:#16A34A"></i></div><b>0%</b>
                        </div>
                        <div><span>Pelaksanaan</span>
                            <div class="orgmate-progress"><i style="width:0%;background:#2563EB"></i></div><b>0%</b>
                        </div>
                        <div><span>Evaluasi</span>
                            <div class="orgmate-progress"><i style="width:0%;background:#F59E0B"></i></div><b>0%</b>
                        </div>
                        <div><span>Pelaporan</span>
                            <div class="orgmate-progress"><i style="width:0%;background:#94A3B8"></i></div><b>0%</b>
                        </div>
                    </div>
                </div>
            </section>
            <section class="orgmate-card">
                <div class="orgmate-card-head">
                    <h2><i data-lucide="megaphone" class="icon"></i> Kegiatan Terbaru</h2><a
                        href="{{ route('simora.e-progress') }}">Lihat Semua →</a>
                </div>
                @forelse ($recentActivities as $activity)
                    <ul class="eprogress-notif">
                        <li>
                            <i style="background:#2563EB"></i>
                            <p>{{ $activity->name }}</p><span>{{ $activity->start_at ? $activity->start_at->locale('id')->translatedFormat('d M Y') : '—' }}</span>
                        </li>
                    </ul>
                @empty
                    <div class="orgmate-empty">Belum ada kegiatan terbaru.</div>
                @endforelse
            </section>
            <section class="orgmate-card">
                <div class="orgmate-card-head">
                    <h2><i data-lucide="bell" class="icon"></i> Notifikasi Terbaru <span class="badge red">3</span></h2><a
                        href="{{ route('simora.e-progress') }}">Lihat Semua →</a>
                </div>
                <ul class="eprogress-notif">
                    <li><i style="background:#2563EB"></i>
                        <p>Jadwal kegiatan akan dimulai</p><span>1 jam lalu</span>
                    </li>
                    <li><i style="background:#16A34A"></i>
                        <p>LPJ telah disetujui</p><span>2 jam lalu</span>
                    </li>
                </ul>
            </section>
        </div>

    </div>
@endsection
