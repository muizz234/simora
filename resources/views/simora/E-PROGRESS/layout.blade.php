<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#092e69">
    <title>{{ $title ?? 'Dashboard Kegiatan' }} | E-PROGRESS SIMORA</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/SIMORA.png') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/orgmate.css?v=4">
    <link rel="stylesheet" href="/assets/css/e-progress.css?v=3">
    <script src="/assets/js/clock.js" defer></script>
    <link rel="stylesheet" href="/assets/css/simora-theme.css?v=2">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
</head>

<body class="orgmate-body eprogress-body">
    <script>
        (function () {
            try {
                if (localStorage.getItem('ft-theme') === 'dark') document.body.classList.add('dark-mode');
            } catch (e) {}
        })();
    </script>
    @php
        $isActive = fn(string $name) => request()->routeIs($name) ? 'is-active' : '';
        $initials = collect(explode(' ', trim(auth()->user()->name)))
            ->map(fn($p) => \Illuminate\Support\Str::substr($p, 0, 1))
            ->take(2)
            ->join('');
    @endphp
    <div class="orgmate-shell" id="orgmateShell">
        <div class="orgmate-overlay" id="orgmateOverlay" hidden></div>

        <aside class="orgmate-sidebar" id="orgmateSidebar">
            <div class="orgmate-brand">
                <img src="{{ asset('assets/e-progress-logo.png') }}" alt="Logo E-PROGRESS">
                <div class="orgmate-brand-text">
                    <strong>E-PROGRESS</strong>
                    <span>Manajemen Kegiatan dan Program Kerja</span>
                </div>
                <button class="orgmate-sidebar-close" id="orgmateSidebarClose" type="button"
                    aria-label="Tutup sidebar"><i data-lucide="x"></i></button>
            </div>

            <nav class="orgmate-nav" aria-label="Navigasi E-PROGRESS">
                <a href="{{ route('simora.e-progress') }}" class="is-active"><i data-lucide="home"></i><span
                        class="orgmate-nav-label">Dashboard Kegiatan</span></a>
                @if(auth()->user()->role === 'developer')
                    {{-- Developer: hanya menu monitoring/manajemen organisasi lintas organisasi --}}
                    <p class="orgmate-nav-group">Monitoring</p>
                    <a href="{{ route('simora.e-progress.monitoring') }}" class="{{ request()->routeIs('simora.e-progress.monitoring') ? 'is-active' : '' }}"><i data-lucide="bar-chart-3"></i><span
                            class="orgmate-nav-label">Monitoring Progres Proker</span></a>
                    @if($organizationsList ?? null)
                        <p class="orgmate-nav-group">Manajemen</p>
                        @foreach($organizationsList as $org)
                            <a href="{{ route('simora.e-progress', ['organization' => $org->slug]) }}"><i data-lucide="building-2"></i><span
                                    class="orgmate-nav-label">{{ $org->name }}</span></a>
                        @endforeach
                    @endif
                @else
                    <p class="orgmate-nav-group">Program Kerja</p>
                    <a href="{{ route('simora.e-progress.proker') }}" class="{{ request()->routeIs('simora.e-progress.proker') ? 'is-active' : '' }}"><i data-lucide="layers"></i><span
                            class="orgmate-nav-label">Proker</span></a>
                    <a href="{{ route('simora.e-progress.kalender') }}" class="{{ request()->routeIs('simora.e-progress.kalender') ? 'is-active' : '' }}"><i data-lucide="calendar-days"></i><span
                            class="orgmate-nav-label">Kalender Kegiatan</span></a>
                    <p class="orgmate-nav-group">Pelaksanaan</p>
                    <a href="{{ route('simora.e-progress.kepanitiaan') }}" class="{{ request()->routeIs('simora.e-progress.kepanitiaan') ? 'is-active' : '' }}"><i data-lucide="users"></i><span
                            class="orgmate-nav-label">Kepanitiaan</span></a>
                    <a href="{{ route('simora.e-progress.presensi') }}" class="{{ request()->routeIs('simora.e-progress.presensi') ? 'is-active' : '' }}"><i data-lucide="qr-code"></i><span
                            class="orgmate-nav-label">Absensi / Presensi (QR Code)</span></a>
                    <a href="{{ route('simora.e-progress.dokumentasi') }}" class="{{ request()->routeIs('simora.e-progress.dokumentasi') ? 'is-active' : '' }}"><i data-lucide="image"></i><span
                            class="orgmate-nav-label">Dokumentasi Kegiatan</span></a>
                    <a href="{{ route('simora.e-progress.evaluasi') }}" class="{{ request()->routeIs('simora.e-progress.evaluasi') ? 'is-active' : '' }}"><i data-lucide="file-check"></i><span
                            class="orgmate-nav-label">Evaluasi &amp; LPJ Kegiatan</span></a>
                    <a href="{{ route('simora.e-progress.monitoring') }}" class="{{ request()->routeIs('simora.e-progress.monitoring') ? 'is-active' : '' }}"><i data-lucide="bar-chart-3"></i><span
                            class="orgmate-nav-label">Monitoring Progres Proker</span></a>
                @endif
            </nav>

            <div class="orgmate-sidebar-foot">
                <div class="orgmate-user-mini">
                    <span class="orgmate-avatar orgmate-avatar-sm">@if (auth()->user()->avatar_url)<img
                            src="{{ auth()->user()->avatar_url }}"
                            alt="Foto profil {{ auth()->user()->name }}">@else{{ strtoupper($initials) }}@endif</span>
                    <div class="orgmate-user-text">
                        <strong>{{ auth()->user()->name }}</strong><span>{{ ucfirst(auth()->user()->role) }}</span>
                    </div>
                    <a href="{{ route('simora.e-progress.panduan') }}" class="orgmate-guide-icon" title="Panduan E-PROGRESS" aria-label="Panduan E-PROGRESS"><i data-lucide="book-open"></i></a>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="orgmate-logout-form">
                    @csrf
                    <button type="submit" class="orgmate-logout"><i
                            data-lucide="log-out"></i><span>Logout</span></button>
                </form>
            </div>
        </aside>

        <div class="orgmate-maincol">
            <header class="orgmate-topbar eprogress-topbar">
                <button class="orgmate-icon-btn" id="orgmateToggle" type="button" aria-label="Toggle sidebar"><i
                        data-lucide="menu"></i></button>
                <nav class="orgmate-breadcrumb" aria-label="Breadcrumb">
                    <a href="{{ route('panel') }}">Dashboard</a><span>›</span><strong>Kegiatan</strong>
                </nav>
                <form class="orgmate-search eprogress-search" method="GET" action="{{ route('simora.e-progress') }}"
                    role="search">
                    <i data-lucide="search"></i>
                    <input type="search" name="q" value="{{ request('q') }}"
                        placeholder="Cari kegiatan, program kerja..." aria-label="Pencarian">
                </form>
                <button class="orgmate-icon-btn simora-theme-toggle" type="button" aria-label="Aktifkan mode gelap"
                    data-theme-toggle><span data-theme-icon>☾</span></button>
                <button class="orgmate-icon-btn orgmate-bell" type="button" aria-label="Notifikasi" id="orgmateBell"><i
                        data-lucide="bell"></i><span class="orgmate-bell-dot">3</span></button>
                <div class="orgmate-profile" id="orgmateProfile">
                    <button class="orgmate-profile-btn" type="button" id="orgmateProfileBtn" aria-haspopup="menu"
                        aria-expanded="false">
                        <span class="orgmate-avatar orgmate-avatar-sm">@if (auth()->user()->avatar_url)<img
                                src="{{ auth()->user()->avatar_url }}"
                                alt="Foto profil {{ auth()->user()->name }}">@else{{ strtoupper($initials) }}@endif</span>
                        <span
                            class="orgmate-profile-meta"><strong>{{ auth()->user()->name }}</strong><small>{{ ucfirst(auth()->user()->role) }}</small></span>
                        <i data-lucide="chevron-down"></i>
                    </button>
                    <div class="orgmate-profile-menu" id="orgmateProfileMenu" role="menu" hidden>
                        <a href="{{ route('panel') }}" role="menuitem"><i data-lucide="layout-dashboard"></i>
                            Kembali ke SIMORA</a>
                        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit"
                                class="orgmate-profile-logout"><i data-lucide="log-out"></i> Logout</button></form>
                    </div>
                </div>
            </header>

            <main class="orgmate-main eprogress-main">
                @yield('content')
            </main>
        </div>
    </div>

    <div class="orgmate-toast" id="orgmateToast" role="status" aria-live="polite" hidden></div>

    <script>
        (function() {
            if (window.lucide) window.lucide.createIcons();
            var shell = document.getElementById('orgmateShell');
            var overlay = document.getElementById('orgmateOverlay');
            var toggle = document.getElementById('orgmateToggle');
            var closeBtn = document.getElementById('orgmateSidebarClose');

            function isMobile() {
                return window.innerWidth <= 900;
            }

            function openMobile() {
                shell.classList.add('is-open');
                overlay.hidden = false;
            }

            function closeMobile() {
                shell.classList.remove('is-open');
                overlay.hidden = true;
            }
            if (toggle) toggle.addEventListener('click', function() {
                if (isMobile()) {
                    shell.classList.contains('is-open') ? closeMobile() : openMobile();
                } else {
                    shell.classList.toggle('is-collapsed');
                    try {
                        localStorage.setItem('eprogress-collapsed', shell.classList.contains('is-collapsed') ?
                            '1' : '0');
                    } catch (e) {}
                }
                if (window.lucide) window.lucide.createIcons();
            });
            if (closeBtn) closeBtn.addEventListener('click', closeMobile);
            if (overlay) overlay.addEventListener('click', closeMobile);
            try {
                if (!isMobile() && localStorage.getItem('eprogress-collapsed') === '1') shell.classList.add(
                    'is-collapsed');
            } catch (e) {}
            if (isMobile()) shell.classList.remove('is-collapsed');
            var profileBtn = document.getElementById('orgmateProfileBtn');
            var profileMenu = document.getElementById('orgmateProfileMenu');
            if (profileBtn && profileMenu) {
                profileBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    var open = profileMenu.hidden;
                    profileMenu.hidden = !open;
                    profileBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
                });
                document.addEventListener('click', function() {
                    profileMenu.hidden = true;
                    profileBtn.setAttribute('aria-expanded', 'false');
                });
            }
            var bell = document.getElementById('orgmateBell');
            var toast = document.getElementById('orgmateToast');
            if (bell) bell.addEventListener('click', function() {
                if (!toast) return;
                toast.textContent = 'Tidak ada notifikasi baru. Data ditampilkan dari database saat ini.';
                toast.hidden = false;
                toast.classList.add('is-show');
                setTimeout(function() {
                    toast.classList.remove('is-show');
                    toast.hidden = true;
                }, 3200);
            });
            window.addEventListener('resize', function() {
                if (!isMobile()) closeMobile();
            });

            // Toggle tema terang/gelap — selaras landing publik (kunci "ft-theme")
            var themeToggle = document.querySelector('[data-theme-toggle]');
            var themeIcon = document.querySelector('[data-theme-icon]');
            function updateThemeButton() {
                var isDark = document.body.classList.contains('dark-mode');
                if (themeIcon) themeIcon.textContent = isDark ? '☀' : '☾';
                if (themeToggle) themeToggle.setAttribute('aria-label', isDark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap');
            }
            updateThemeButton();
            if (themeToggle) themeToggle.addEventListener('click', function() {
                document.body.classList.toggle('dark-mode');
                try {
                    localStorage.setItem('ft-theme', document.body.classList.contains('dark-mode') ? 'dark' : 'light');
                } catch (e) {}
                updateThemeButton();
            });
        })();
    </script>
    @yield('scripts')
</body>

</html>
