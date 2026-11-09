@extends('simora.ORGMATE.layout', ['title' => 'Dashboard Organisasi'])
@section('breadcrumb', 'Dashboard Organisasi')

@section('content')
    {{-- ===== Header sapaan seperti contoh ===== --}}
    <div class="orgmate-hero">
        <div>
            <h1>Selamat Datang, {{ auth()->user()->name }} 👋</h1>
            <p class="lead">Kelola organisasi{{ $orgName ? ' ' . $orgName : '' }} dengan lebih mudah dan terintegrasi</p>
        </div>
        <div class="orgmate-hero-datebox">
            <a class="orgmate-btn" href="{{ route('simora.orgmate.panduan') }}" style="margin-bottom:10px"><i data-lucide="book-open"></i> Panduan ORGMATE</a>
            <span class="orgmate-hero-date"><i data-lucide="calendar-days"></i><span
                    data-live-date>{{ $todayLabel }}</span></span>
            <span class="orgmate-hero-date"><i data-lucide="clock"></i><span data-live-clock>—</span></span>
            @php $orgPeriod = $selectedOrganization?->period ?? auth()->user()->organization?->period ?? ($organizationsList ?? collect())->first()?->period; @endphp
            @if (!empty($orgPeriod))
                <small class="orgmate-hijri">Periode: {{ $orgPeriod }}</small>
            @endif
            @if (!empty($hijriLabel))
                <small class="orgmate-hijri">{{ $hijriLabel }} H</small>
            @endif
            @if (($isDeveloper ?? false) && ($organizationsList ?? collect())->count() > 0)
                <form method="GET" action="{{ route('simora.orgmate') }}" class="orgmate-org-form">
                    <label for="orgmateOrgSelect">Organisasi:</label>
                    <select id="orgmateOrgSelect" name="organization" onchange="this.form.submit()">
                        <option value="">Semua Organisasi</option>
                        @foreach ($organizationsList as $org)
                            <option value="{{ $org->slug }}"
                                {{ $selectedOrganization?->slug === $org->slug ? 'selected' : '' }}>{{ $org->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif
        </div>
    </div>

    {{-- ===== 4 kartu berwarna seperti contoh ===== --}}
    <div class="orgmate-stats">
        <article class="orgmate-stat orgmate-stat--blue">
            <span class="orgmate-stat-icon"><i data-lucide="users"></i></span>
            <div><strong>{{ number_format($totalAnggota) }}</strong><span class="lbl">Total Anggota</span></div>
            <span class="orgmate-stat-delta">+2%</span>
        </article>
        <article class="orgmate-stat orgmate-stat--purple">
            <span class="orgmate-stat-icon"><i data-lucide="network"></i></span>
            <div><strong>{{ number_format($totalDivisi) }}</strong><span class="lbl">Divisi</span></div>
            <span class="orgmate-stat-delta">→ 0%</span>
        </article>
        <article class="orgmate-stat orgmate-stat--green">
            <span class="orgmate-stat-icon"><i data-lucide="file-text"></i></span>
            <div><strong>{{ number_format($totalDokumen) }}</strong><span class="lbl">Dokumen Arsip</span></div>
            <span class="orgmate-stat-delta">+1%</span>
        </article>
        <article class="orgmate-stat orgmate-stat--amber">
            <span class="orgmate-stat-icon"><i data-lucide="megaphone"></i></span>
            <div><strong>{{ number_format($totalPengumuman) }}</strong><span class="lbl">Pengumuman Aktif</span></div>
            <span class="orgmate-stat-delta">→ 0%</span>
        </article>
    </div>

    {{-- ===== Baris tengah: statistik + struktur ===== --}}
    <div class="orgmate-row-2">
        <section class="orgmate-card">
            <div class="orgmate-card-head">
                <h2><i data-lucide="bar-chart-3"></i> Statistik Organisasi</h2>
                <span class="badge gray">Tahun Ini</span>
            </div>
            @if ($totalAnggota === 0)
                <div class="orgmate-empty">Belum ada data anggota. Grafik akan terisi otomatis setelah ada akun anggota.
                </div>
            @else
                <div class="orgmate-chart-box"><canvas id="chartAnggota" aria-label="Grafik statistik organisasi"></canvas>
                </div>
            @endif
        </section>

        <section class="orgmate-card">
            <div class="orgmate-card-head">
                <h2><i data-lucide="network"></i> Struktur Organisasi</h2>
                <a href="{{ route('simora.orgmate.struktur') }}">Lihat Detail →</a>
            </div>
            <div class="orgmate-orgchart">
                <div class="och-box och--ketua"><span>Ketua Umum</span><strong>Belum terisi</strong></div>
                <div class="och-line"></div>
                <div class="och-box och--wakil"><span>Wakil Ketua Umum</span><strong>Belum terisi</strong></div>
                <div class="och-line"></div>
                <div class="och-row-3">
                    <div class="och-box"><span>Sekretaris Umum</span><strong>Belum terisi</strong></div>
                    <div class="och-box"><span>Bendahara Umum</span><strong>Belum terisi</strong></div>
                    <div class="och-box och--divisi"><span>Divisi-Divisi</span><strong>Lainnya</strong></div>
                </div>
            </div>
            <p class="orgmate-hint">Nama pengurus/divisi belum terisi di database — kotak ditampilkan sesuai struktur
                contoh.</p>
        </section>
    </div>

    {{-- ===== Baris bawah: anggota terbaru, pengumuman, menu cepat ===== --}}
    <div class="orgmate-row-3">
        <section class="orgmate-card">
            <div class="orgmate-card-head">
                <h2><i data-lucide="users"></i> Data Anggota Terbaru</h2>
                <a href="{{ route('simora.orgmate.anggota') }}">Lihat Semua →</a>
            </div>
            @if ($recentMembers->isEmpty())
                <div class="orgmate-empty">Belum ada data anggota.</div>
            @else
                <ul class="orgmate-list">
                    @foreach ($recentMembers as $member)
                        <li>
                            <span
                                class="orgmate-avatar orgmate-avatar-sm">@if ($member->avatar_url)<img src="{{ $member->avatar_url }}"
                                        alt="Foto {{ $member->name }}">@else{{ strtoupper(substr($member->name, 0, 1)) }}@endif</span>
                            <div class="orgmate-list-body">
                                <strong>{{ $member->name }}</strong>
                                <small>{{ $member->organization?->name ?? $member->email }}</small>
                            </div>
                            @if ($member->email_verified_at)
                                <span class="badge green">Aktif</span>
                            @else
                                <span class="badge amber">Nonaktif</span>
                            @endif
                            <span
                                class="orgmate-time">{{ $member->created_at?->locale('id')->diffForHumans() ?? '—' }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
            <div class="orgmate-actions" style="margin-top:12px">
                <a class="orgmate-btn" href="{{ route('simora.orgmate.anggota') }}"><i data-lucide="user-plus"></i> Tambah
                    Anggota</a>
            </div>
        </section>

        <section class="orgmate-card">
            <div class="orgmate-card-head">
                <h2><i data-lucide="megaphone"></i> Pengumuman Terbaru</h2>
                <a href="{{ route('simora.orgmate.pengumuman') }}">Lihat Semua →</a>
            </div>
            <div class="orgmate-empty">Belum ada pengumuman internal.</div>
            <div class="orgmate-list" style="margin-top:12px">
                <div class="orgmate-post"><span class="orgmate-post-icon"><i data-lucide="info"></i></span>
                    <div>
                        <h3>Contoh format pengumuman</h3>
                        <p>Judul, isi singkat, dan tanggal publikasi akan tampil di sini setelah data tersedia.</p>
                    </div>
                </div>
            </div>
            <div class="orgmate-actions">
                <a class="orgmate-btn ghost" href="{{ route('simora.orgmate.pengumuman') }}"><i data-lucide="plus"></i>
                    Buat Pengumuman</a>
            </div>
        </section>

        <section class="orgmate-card">
            <div class="orgmate-card-head">
                <h2><i data-lucide="zap"></i> Menu Cepat</h2>
            </div>
            <div class="orgmate-quick orgmate-quick-3">
                <a href="{{ route('simora.orgmate.struktur') }}"><span class="q-icon"><i
                            data-lucide="network"></i></span>Struktur Organisasi</a>
                <a href="{{ route('simora.orgmate.anggota') }}"><span class="q-icon"><i
                            data-lucide="user-plus"></i></span>Tambah Anggota</a>
                <a href="{{ route('simora.orgmate.rekrutmen') }}"><span class="q-icon"><i
                            data-lucide="clipboard-list"></i></span>Pendaftaran Anggota</a>
                <a href="{{ route('simora.orgmate.arsip') }}"><span class="q-icon"><i
                            data-lucide="upload"></i></span>Upload Dokumen</a>
                <a href="{{ route('simora.orgmate.pengumuman') }}"><span class="q-icon"><i
                            data-lucide="megaphone"></i></span>Buat Pengumuman</a>
                <a href="{{ route('simora.orgmate.arsip') }}"><span class="q-icon"><i
                            data-lucide="folder-open"></i></span>Lihat Arsip</a>
            </div>
        </section>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var dateEl = document.getElementById('orgmateDate');
            if (dateEl) {
                try {
                    dateEl.textContent = new Intl.DateTimeFormat('id-ID', {
                        weekday: 'long',
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric'
                    }).format(new Date());
                } catch (e) {}
            }
            if (!window.Chart) return;
            Chart.defaults.font.family = "'Plus Jakarta Sans', Inter, Arial, sans-serif";
            Chart.defaults.color = '#94a3b8';
            var c1 = document.getElementById('chartAnggota');
            if (c1) {
                new Chart(c1, {
                    type: 'line',
                    data: {
                        labels: @json($bulan),
                        datasets: [{
                                label: 'Anggota Aktif',
                                data: @json($anggotaAktifSeries),
                                borderColor: '#2563EB',
                                backgroundColor: 'rgb(37 99 235 / .08)',
                                fill: true,
                                tension: .4,
                                pointRadius: 3,
                                pointBackgroundColor: '#2563EB'
                            },
                            {
                                label: 'Pendaftaran Baru',
                                data: @json($pendaftaranBaru),
                                borderColor: '#7C3AED',
                                tension: .4,
                                pointRadius: 3,
                                pointBackgroundColor: '#7C3AED'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 8
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                },
                                grid: {
                                    color: '#eef2f7'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
@endsection
