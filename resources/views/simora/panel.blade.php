<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f5f7fb">
    <title>Dashboard | SIMORA</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/SIMORA.png') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="/assets/css/panel.css?v=5">
    <script src="/assets/js/clock.js" defer></script>
</head>
<body class="panel-page">
    <x-panel-header active="dashboard" />
    <main class="panel-main">
        {{-- ===== Hero sambutan ===== --}}
        <section class="panel-hero">
            <div class="panel-hero-copy">
                <p class="panel-kicker">SIMORA · PANEL PENGEMBANG — {{ $greeting }},</p>
                <h1>{{ $user->name }}</h1>
                <p class="panel-hero-sub">Selamat datang di SIMORA. Kelola landing page dan pengembangan SIMORA.</p>
                <div class="panel-hero-actions">
                    <a class="panel-action panel-action--primary" href="{{ route('simora.organizations.create') }}"><span>＋</span> Tambah Organisasi</a>
                    <a class="panel-action" href="{{ route('simora.accounts.create') }}"><span>＋</span> Tambah Akun</a>
                    <a class="panel-action" href="{{ route('simora.landing-management') }}"><span>◐</span> Kelola Landing</a>
                </div>
            </div>
            <div class="panel-hero-meta">
                <span class="panel-hero-date">{{ $todayLabel }}</span>
                <span class="panel-hero-clock" data-live-clock>—</span>
                <span class="access-badge"><i></i> Akses {{ ucfirst($user->role ?? 'user') }}</span>
            </div>
            <div class="panel-hero-decor" aria-hidden="true"><i class="d1"></i><i class="d2"></i></div>
        </section>

        {{-- ===== Statistik ringkas ===== --}}
        <section class="panel-stats" aria-label="Statistik SIMORA">
            <article class="panel-stat panel-stat--blue">
                <span class="panel-stat-icon">♧</span>
                <div><strong data-count="{{ $stats['organisasi'] }}">0</strong><small>Organisasi</small></div>
            </article>
            <article class="panel-stat panel-stat--purple">
                <span class="panel-stat-icon">👥</span>
                <div><strong data-count="{{ $stats['akun'] }}">0</strong><small>Total Akun</small></div>
            </article>
            <article class="panel-stat panel-stat--green">
                <span class="panel-stat-icon">✦</span>
                <div><strong data-count="{{ $stats['akunOrganisasi'] }}">0</strong><small>Akun Organisasi</small></div>
            </article>
            <article class="panel-stat panel-stat--amber">
                <span class="panel-stat-icon">▤</span>
                <div><strong data-count="{{ $stats['proker'] }}">0</strong><small>Program Kerja</small></div>
            </article>
        </section>

        <div class="panel-split">
            {{-- ===== Organisasi terbaru ===== --}}
            <section class="panel-card">
                <div class="panel-card-head">
                    <h2>Organisasi Terbaru</h2>
                    <a href="{{ route('simora.organizations.index') }}">Lihat Semua →</a>
                </div>
                @forelse ($recentOrganizations as $organization)
                    <a class="panel-recent" href="{{ route('simora.organizations.edit', $organization) }}">
                        @if ($organization->logo_url)
                            <img src="{{ $organization->logo_url }}" alt="Logo {{ $organization->name }}">
                        @else
                            <span class="panel-recent-avatar">{{ strtoupper(substr($organization->name, 0, 2)) }}</span>
                        @endif
                        <span class="panel-recent-body"><strong>{{ $organization->name }}</strong><small>{{ $organization->full_name }}</small></span>
                        <span class="panel-recent-period">{{ $organization->period ?? '—' }}</span>
                        <span class="panel-recent-arrow">↗</span>
                    </a>
                @empty
                    <p class="panel-empty">Belum ada organisasi. Tambahkan organisasi pertama.</p>
                @endforelse
            </section>

            {{-- ===== Profil akun ===== --}}
            <aside class="panel-card panel-profile-card">
                <div class="panel-card-head">
                    <h2>Profil Akun</h2>
                </div>
                <div class="panel-profile-top">
                    <span class="developer-avatar developer-avatar--lg">@if (auth()->user()->avatar_url)<img src="{{ auth()->user()->avatar_url }}" alt="Foto profil {{ auth()->user()->name }}">@else{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}@endif</span>
                    <div>
                        <strong>{{ $user->name }}</strong>
                        <small>{{ $user->email }}</small>
                    </div>
                </div>
                <div class="developer-details panel-details">
                    <div><span>Portal</span><strong>SIMORA</strong></div>
                    <div><span>Instansi</span><strong>FT UNUJA</strong></div>
                    <div><span>Organisasi</span><strong>{{ $user->organization?->name ?? '—' }}</strong></div>
                </div>
                <a class="panel-action panel-action--block" href="{{ route('simora.accounts.index') }}">Kelola Akun <span>→</span></a>
            </aside>
        </div>

        {{-- ===== Workspace modul ===== --}}
        <section class="modules-section" id="workspace">
            <div class="modules-heading"><div><p class="panel-kicker">AREA PENGEMBANGAN</p><h2>Workspace</h2></div><span>3 area</span></div>
            <div class="module-grid">
                <a class="module-card orgmate" href="{{ route('simora.orgmate') }}"><span class="module-symbol"><img src="{{ asset('assets/orgmate-logo.png') }}" alt="Logo ORGMATE" class="module-logo"></span><span><span class="module-index">WORKSPACE 01</span><h3>ORGMATE</h3><p>Manajemen Organisasi dan Keanggotaan (Master Data)</p></span><span class="module-arrow">↗</span></a>
                <a class="module-card eprogress" href="{{ route('simora.e-progress') }}"><span class="module-symbol"><img src="{{ asset('assets/e-progress-logo.png') }}" alt="Logo E-PROGRESS" class="module-logo"></span><span><span class="module-index">WORKSPACE 02</span><h3>E-PROGRESS</h3><p>Manajemen Kegiatan dan Program Kerja</p></span><span class="module-arrow">↗</span></a>
                <a class="module-card sentra" href="{{ route('simora.sentra') }}"><span class="module-symbol"><img src="{{ asset('assets/sentra-logo.png') }}" alt="Logo SENTRA" class="module-logo"></span><span><span class="module-index">WORKSPACE 03</span><h3>SENTRA</h3><p>Manajemen Keuangan</p></span><span class="module-arrow">↗</span></a>
            </div>
        </section>
    </main>

    <footer class="panel-footer"><span>SIMORA · Fakultas Teknik Universitas Nurul Jadid</span><span>Kolaborasi <i>•</i> Inovasi <i>•</i> Prestasi</span></footer>

    <script>
        // Animasi angka statistik (count-up).
        document.querySelectorAll('[data-count]').forEach((el) => {
            const target = Number(el.dataset.count || 0);
            const duration = 700;
            const start = performance.now();
            const step = (now) => {
                const progress = Math.min((now - start) / duration, 1);
                el.textContent = Math.round(target * progress).toLocaleString('id-ID');
                if (progress < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        });
    </script>
</body>
</html>
