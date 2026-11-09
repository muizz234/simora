<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f5f8ff">
    <title>Organisasi Kemahasiswaan Fakultas Teknik | UNUJA</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/Logo FT.png') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/landing.css?v=3">
    <script src="/assets/js/landing.js?v=2" defer></script>
    <script src="/assets/js/clock.js" defer></script>
</head>

<body>
    <header class="site-header" id="beranda">
        <a class="brand" href="#beranda" aria-label="Beranda Fakultas Teknik UNUJA">
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
            <a class="nav-link active" href="#beranda">Beranda</a>
            <a class="nav-link" href="{{ route('struktur.index') }}">Struktural</a>
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

    <main>
        <section class="hero" aria-labelledby="hero-title">
            <div class="hero-blueprint" aria-hidden="true"></div>
            <div class="hero-copy">
                <p class="eyebrow"><span></span> Fakultas Teknik · Universitas Nurul Jadid <span></span></p>
                <h1 id="hero-title">ORGANISASI KEMAHASISWAAN<br><strong>FAKULTAS TEKNIK</strong></h1>
                <p class="hero-tagline">“Bersama Membangun Fakultas Teknik yang Lebih Baik”</p>
                <a href="#organisasi" class="hero-cta">Jelajahi organisasi <span aria-hidden="true">↓</span></a>
            </div>
            <div class="faculty-seal" aria-label="Logo Fakultas Teknik Universitas Nurul Jadid">
                <img src="{{ asset('assets/Logo FT.png') }}" alt="Logo Fakultas Teknik">
            </div>
            <div class="unuja-mark" aria-label="Logo Universitas Nurul Jadid">
                <img src="{{ asset('assets/logo-unuja.png') }}" alt="Logo Universitas Nurul Jadid">
            </div>
            <div class="hero-campus" aria-hidden="true">
                <div class="campus-skyline">
                    <span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span>
                </div>
                <div class="campus-label">FAKULTAS TEKNIK</div>
                <div class="campus-ground"></div>
            </div>
            <div class="hero-orbit orbit-one"></div>
            <div class="hero-orbit orbit-two"></div>
            <div class="hero-index"><span>01</span><i></i> RUANG BERTUMBUH</div>
        </section>

        <section class="organizations section-wrap" id="organisasi">
            <div class="section-heading">
                <div>
                    <p class="section-kicker">SATU FAKULTAS, BANYAK KARYA</p>
                    <h2>Kenali <span>organisasi kami.</span></h2>
                </div>
                <p>Ruang kolaborasi mahasiswa Fakultas Teknik untuk belajar, berkarya, dan memberi dampak.</p>
            </div>
            <div class="organization-list" data-organization-list>
                @foreach ($organizations as $index => $organization)
                    <x-organization-card :organization="$organization" :index="$index + 1" />
                @endforeach
                <p class="empty-search" hidden data-empty-search>Organisasi tidak ditemukan. Coba kata kunci lain.</p>
            </div>
        </section>

        <section class="news-placeholder section-wrap" id="rekrutmen">
            <div class="news-mark">GABUNG<br>ORGANISASI</div>
            @if ($organizations->isNotEmpty())
                <div class="recruitment-grid">
                    @foreach ($organizations as $organization)
                        <article class="recruitment-card"
                            style="--org-color: {{ $organization->banner_color }}; --org-accent: {{ $organization->accent_color }}"
                            area-label="{{ $organization->name }}">
                            <img src="{{ $organization->logo_url }}" alt="Logo {{ $organization->name }}">
                            @if ($organization->registration_url)
                                <a href="{{ $organization->registration_url }}" target="_blank" rel="noreferrer">Link Pendaftaran ↗</a>
                            @else
                                <span class="recruitment-disabled">Link pendaftaran belum tersedia</span>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="community-strip" id="kegiatan">
            <div class="strip-number">FT<span>/</span>UNUJA</div>
            <div>
                <p>RUANG UNTUK BERGERAK</p>
                <h2>Ide baik tumbuh saat dikerjakan bersama.</h2>
            </div>
            <a href="#kontak" aria-label="Hubungi Fakultas Teknik">Mari berkolaborasi <span>↗</span></a>
        </section>
    </main>

    @include('partials.site-footer')
</body>

</html>
