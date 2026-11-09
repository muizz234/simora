<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo-head')
    <meta name="theme-color" content="#092e69">
    <title>{{ $title ?? 'Dashboard Keuangan' }} | SENTRA SIMORA</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/sentra-logo.png') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    @vite(['resources/css/simora/module/module.css', 'resources/css/simora/orgmate/orgmate.css', 'resources/css/simora/sentra/sentra.css', 'resources/css/simora/theme/simora-theme.css', 'resources/js/clock.js', 'resources/js/simora/module.js'])
    {{-- Lucide: dimuat lokal via module.js (Vite), tanpa CDN --}}
</head>

<body class="orgmate-body sentra-body">
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
    <div class="orgmate-shell" id="orgmateShell" data-storage-key="sentra-collapsed">
        <div class="orgmate-overlay" id="orgmateOverlay" hidden></div>

        <aside class="orgmate-sidebar sentra-sidebar" id="orgmateSidebar">
            <div class="orgmate-brand sentra-brand">
                <img class="sentra-brand-logo" src="{{ asset('assets/sentra-logo.png') }}" alt="Logo SENTRA">
                <div class="orgmate-brand-text">
                    <strong>SENTRA</strong>
                    <span>Sistem Manajemen Keuangan</span>
                </div>
                <button class="orgmate-sidebar-close" id="orgmateSidebarClose" type="button"
                    aria-label="Tutup sidebar"><i data-lucide="x" class="icon"></i></button>
            </div>

            <nav class="orgmate-nav" aria-label="Navigasi SENTRA">
                <a href="{{ route('simora.sentra') }}" class="{{ $isActive('simora.sentra') }}"><i
                        data-lucide="layout-grid"></i><span class="orgmate-nav-label">Dashboard</span></a>
                @if(auth()->user()->role === 'developer')
                    {{-- Developer: mode monitoring/manajemen organisasi lintas organisasi, seperti E-PROGRESS --}}
                    <p class="orgmate-nav-group">Monitoring</p>
                    <a href="{{ route('simora.sentra') }}"><i data-lucide="chart-column" class="icon"></i><span
                            class="orgmate-nav-label">Monitoring Keuangan</span></a>
                    @if($organizationsList ?? null)
                        <p class="orgmate-nav-group">Manajemen</p>
                        @foreach($organizationsList as $org)
                            <a href="{{ route('simora.sentra', ['organization' => $org->slug]) }}"><i data-lucide="building-2" class="icon"></i><span
                                    class="orgmate-nav-label">{{ $org->name }}</span></a>
                        @endforeach
                    @endif
                @endif
                <a href="{{ route('simora.sentra.kas') }}" class="{{ $isActive('simora.sentra.kas') }}"><i
                        data-lucide="wallet"></i><span class="orgmate-nav-label">Kas Organisasi</span></a>
                <a href="{{ route('simora.sentra.rab') }}" class="{{ $isActive('simora.sentra.rab') }}"><i
                        data-lucide="clipboard-list"></i><span class="orgmate-nav-label">Anggaran &amp; RAB</span></a>
                @if(auth()->user()->role !== 'developer')
                    {{-- Iuran Anggota bersifat per-anggota; tidak ditampilkan untuk developer (manajemen organisasi) --}}
                    <a href="{{ route('simora.sentra.iuran') }}" class="{{ $isActive('simora.sentra.iuran') }}"><i
                            data-lucide="users"></i><span class="orgmate-nav-label">Iuran Anggota</span></a>
                @endif
                <a href="{{ route('simora.sentra.realisasi') }}" class="{{ $isActive('simora.sentra.realisasi') }}"><i
                        data-lucide="receipt-text"></i><span class="orgmate-nav-label">Realisasi Anggaran</span></a>
                <a href="{{ route('simora.sentra.laporan') }}" class="{{ $isActive('simora.sentra.laporan') }}"><i
                        data-lucide="file-text"></i><span class="orgmate-nav-label">Laporan Keuangan</span></a>
            </nav>

            <div class="orgmate-sidebar-foot">
                <div class="orgmate-user-mini">
                    <span class="orgmate-avatar orgmate-avatar-sm">@if (auth()->user()->avatar_url)<img
                            src="{{ auth()->user()->avatar_url }}"
                            alt="Foto profil {{ auth()->user()->name }}">@else{{ strtoupper($initials) }}@endif</span>
                    <div class="orgmate-user-text">
                        <strong>{{ auth()->user()->name }}</strong><span>{{ ucfirst(auth()->user()->role) }}</span>
                    </div>
                    <a href="{{ route('simora.sentra.panduan') }}" class="orgmate-guide-icon" title="Panduan SENTRA" aria-label="Panduan SENTRA"><i data-lucide="book-open" class="icon"></i></a>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="orgmate-logout-form">
                    @csrf
                    <button type="submit" class="orgmate-logout"><i
                            data-lucide="log-out"></i><span>Logout</span></button>
                </form>
            </div>
        </aside>

        <div class="orgmate-maincol">
            @include('simora.partials.module-topbar', ['topbarAction' => route('simora.sentra.kas'), 'topbarPlaceholder' => 'Cari fitur atau data...', 'topbarClass' => 'sentra-topbar', 'searchClass' => 'sentra-search', 'bellBadge' => 3])


            <main class="orgmate-main sentra-main">
                @yield('content')
            </main>
        </div>
    </div>

    <div class="orgmate-toast" id="orgmateToast" role="status" aria-live="polite" hidden></div>

    
    @yield('scripts')
</body>

</html>
