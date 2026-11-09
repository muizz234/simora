<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo-head')
    <meta name="theme-color" content="#f5f8ff">
    <title>Struktural Organisasi | Fakultas Teknik UNUJA</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/Logo FT.png') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    @vite(['resources/css/landing/landing.css', 'resources/css/struktur/struktur.css', 'resources/js/landing/landing.js', 'resources/js/clock.js'])
</head>

<body>
    <header class="site-header" id="beranda">
        <a class="brand" href="{{ route('home') }}" aria-label="Beranda Fakultas Teknik UNUJA">
            <img src="{{ asset('assets/Logo FT.png') }}" alt="Logo Fakultas Teknik Universitas Nurul Jadid">
            <span><strong>Fakultas Teknik</strong><small>Universitas Nurul Jadid</small></span>
        </a>
        <a class="simora-button" href="{{ route('login') }}" aria-label="Login SSO SIMORA" title="Login SSO SIMORA">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                <path d="m10 17 5-5-5-5M15 12H3"></path>
            </svg>
            <span>SIMORA</span>
        </a>
        <button class="menu-toggle" type="button" aria-label="Buka menu" aria-expanded="false"
            data-menu-toggle><span></span><span></span><span></span></button>
        <nav class="main-nav" data-navigation aria-label="Navigasi utama">
            <a class="nav-link" href="{{ route('home') }}#beranda">Beranda</a>
            <a class="nav-link active" href="{{ route('struktur.index') }}" aria-current="page">Struktural</a>
            <a class="nav-link" href="{{ route('kegiatan.index') }}">Kegiatan</a>
            <a class="nav-link" href="{{ route('kontak') }}">Kontak</a>
        </nav>
        <label class="search-box" aria-label="Cari organisasi">
            <input type="search" placeholder="Cari Organisasi..." autocomplete="off" data-organization-search>
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="10.8" cy="10.8" r="6.8"></circle>
                <path d="m16 16 5 5"></path>
            </svg>
        </label>
        <button class="theme-toggle" type="button" aria-label="Aktifkan mode gelap" data-theme-toggle><span
                data-theme-icon>☾</span></button>
        <span class="landing-clock" data-live-clock aria-live="off">—</span>
    </header>
    <main class="struktur-main">
        <div class="struktur-intro">
            <p class="panel-kicker">STRUKTUR ORGANISASI · FAKULTAS TEKNIK</p>
            <p>Pilih organisasi untuk melihat struktur lengkapnya.</p>
        </div>
        <div class="struktur-grid">
            @foreach ($organizations as $organization)
                <a class="struktur-card" href="{{ route('struktur.show', $organization) }}"
                    style="--org-color: {{ $organization->banner_color }}">
                    <span class="struktur-logo"><img src="{{ $organization->logo_url }}"
                            alt="Logo {{ $organization->name }}"></span>
                    <strong>{{ $organization->name }}</strong>
                    <span>{{ $organization->full_name }}</span>
                    <small>Lihat struktur →</small>
                </a>
            @endforeach
        </div>
    </main>
    @include('partials.site-footer')
</body>

</html>
