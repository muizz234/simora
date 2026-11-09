<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo-head')
    <meta name="theme-color" content="#092e69">
    <title>{{ $title ?? 'Dashboard Kegiatan' }} | E-PROGRESS SIMORA</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/SIMORA.png') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    @vite(['resources/css/simora/module/module.css', 'resources/css/simora/orgmate/orgmate.css', 'resources/css/simora/e-progress/e-progress.css', 'resources/css/simora/theme/simora-theme.css', 'resources/js/clock.js', 'resources/js/simora/module.js'])
    {{-- Lucide: dimuat lokal via module.js (Vite), tanpa CDN --}}
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
    <div class="orgmate-shell" id="orgmateShell" data-storage-key="eprogress-collapsed">
        <div class="orgmate-overlay" id="orgmateOverlay" hidden></div>

        <aside class="orgmate-sidebar" id="orgmateSidebar">
            <div class="orgmate-brand">
                <img src="{{ asset('assets/e-progress-logo.png') }}" alt="Logo E-PROGRESS">
                <div class="orgmate-brand-text">
                    <strong>E-PROGRESS</strong>
                    <span>Manajemen Kegiatan dan Program Kerja</span>
                </div>
                <button class="orgmate-sidebar-close" id="orgmateSidebarClose" type="button"
                    aria-label="Tutup sidebar"><i data-lucide="x" class="icon"></i></button>
            </div>

            <nav class="orgmate-nav" aria-label="Navigasi E-PROGRESS">
                <a href="{{ route('simora.e-progress') }}" class="is-active"><i data-lucide="house" class="icon"></i><span
                        class="orgmate-nav-label">Dashboard Kegiatan</span></a>
                @if(auth()->user()->role === 'developer')
                    {{-- Developer: hanya menu monitoring/manajemen organisasi lintas organisasi --}}
                    <p class="orgmate-nav-group">Monitoring</p>
                    <a href="{{ route('simora.e-progress.monitoring') }}" class="{{ request()->routeIs('simora.e-progress.monitoring') ? 'is-active' : '' }}"><i data-lucide="chart-column" class="icon"></i><span
                            class="orgmate-nav-label">Monitoring Progres Proker</span></a>
                    @if($organizationsList ?? null)
                        <p class="orgmate-nav-group">Manajemen</p>
                        @foreach($organizationsList as $org)
                            <a href="{{ route('simora.e-progress', ['organization' => $org->slug]) }}"><i data-lucide="building-2" class="icon"></i><span
                                    class="orgmate-nav-label">{{ $org->name }}</span></a>
                        @endforeach
                    @endif
                @else
                    <p class="orgmate-nav-group">Program Kerja</p>
                    <a href="{{ route('simora.e-progress.proker') }}" class="{{ request()->routeIs('simora.e-progress.proker') ? 'is-active' : '' }}"><i data-lucide="layers" class="icon"></i><span
                            class="orgmate-nav-label">Proker</span></a>
                    <a href="{{ route('simora.e-progress.kalender') }}" class="{{ request()->routeIs('simora.e-progress.kalender') ? 'is-active' : '' }}"><i data-lucide="calendar-days" class="icon"></i><span
                            class="orgmate-nav-label">Kalender Kegiatan</span></a>
                    <p class="orgmate-nav-group">Pelaksanaan</p>
                    <a href="{{ route('simora.e-progress.kepanitiaan') }}" class="{{ request()->routeIs('simora.e-progress.kepanitiaan') ? 'is-active' : '' }}"><i data-lucide="users" class="icon"></i><span
                            class="orgmate-nav-label">Kepanitiaan</span></a>
                    <a href="{{ route('simora.e-progress.presensi') }}" class="{{ request()->routeIs('simora.e-progress.presensi') ? 'is-active' : '' }}"><i data-lucide="qr-code" class="icon"></i><span
                            class="orgmate-nav-label">Absensi / Presensi (QR Code)</span></a>
                    <a href="{{ route('simora.e-progress.dokumentasi') }}" class="{{ request()->routeIs('simora.e-progress.dokumentasi') ? 'is-active' : '' }}"><i data-lucide="image" class="icon"></i><span
                            class="orgmate-nav-label">Dokumentasi Kegiatan</span></a>
                    <a href="{{ route('simora.e-progress.evaluasi') }}" class="{{ request()->routeIs('simora.e-progress.evaluasi') ? 'is-active' : '' }}"><i data-lucide="file-check" class="icon"></i><span
                            class="orgmate-nav-label">Evaluasi &amp; LPJ Kegiatan</span></a>
                    <a href="{{ route('simora.e-progress.monitoring') }}" class="{{ request()->routeIs('simora.e-progress.monitoring') ? 'is-active' : '' }}"><i data-lucide="chart-column" class="icon"></i><span
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
                    <a href="{{ route('simora.e-progress.panduan') }}" class="orgmate-guide-icon" title="Panduan E-PROGRESS" aria-label="Panduan E-PROGRESS"><i data-lucide="book-open" class="icon"></i></a>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="orgmate-logout-form">
                    @csrf
                    <button type="submit" class="orgmate-logout"><i
                            data-lucide="log-out"></i><span>Logout</span></button>
                </form>
            </div>
        </aside>

        <div class="orgmate-maincol">
            @include('simora.partials.module-topbar', ['topbarAction' => route('simora.e-progress'), 'topbarPlaceholder' => 'Cari kegiatan, program kerja...', 'topbarClass' => 'eprogress-topbar', 'searchClass' => 'eprogress-search', 'breadcrumb' => 'Kegiatan', 'bellBadge' => 3])


            <main class="orgmate-main eprogress-main">
                @yield('content')
            </main>
        </div>
    </div>

    <div class="orgmate-toast" id="orgmateToast" role="status" aria-live="polite" hidden></div>

    
    @yield('scripts')
</body>

</html>
