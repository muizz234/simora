<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f5f8ff">
    <title>Kontak Organisasi | Fakultas Teknik UNUJA</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/Logo FT.png') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/landing.css?v=2">
    <link rel="stylesheet" href="/assets/css/kontak.css?v=2">
    <script src="/assets/js/landing.js?v=2" defer></script>
</head>

<body>
    <header class="site-header" id="beranda">
        <a class="brand" href="{{ route('home') }}#beranda" aria-label="Beranda Fakultas Teknik UNUJA">
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
            <a class="nav-link" href="{{ route('struktur.index') }}">Struktural</a>
            <a class="nav-link" href="{{ route('home') }}#kegiatan">Kegiatan</a>
            <a class="nav-link active" href="{{ route('kontak') }}" aria-current="page">Kontak</a>
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
    </header>
    <main class="kontak-main">
        <div class="kontak-intro">
            <p class="panel-kicker">KONTAK ORGANISASI · FAKULTAS TEKNIK</p>
            <p>Pilih organisasi untuk menghubungi melalui sosial media resmi.</p>
        </div>
        <div class="kontak-grid">
            @foreach ($organizations as $organization)
                <article class="kontak-card"
                    style="--org-color: {{ $organization->banner_color }}; --org-accent: {{ $organization->accent_color }}">
                    <div class="kontak-logo"><img src="{{ $organization->logo_url }}"
                            alt="Logo {{ $organization->name }}"></div>
                    <strong>{{ $organization->name }}</strong>
                    <span>{{ $organization->full_name }}</span>
                    <div class="kontak-socials">
                        @if ($organization->instagram_url)
                            <a href="{{ $organization->instagram_url }}" target="_blank" rel="noreferrer"
                                aria-label="Instagram {{ $organization->name }}"><svg viewBox="0 0 24 24">
                                    <rect x="3" y="3" width="18" height="18" rx="5"></rect>
                                    <circle cx="12" cy="12" r="4"></circle>
                                    <circle cx="17.2" cy="6.8" r="0.8" fill="currentColor"></circle>
                                </svg></a>
                        @endif
                        @if ($organization->tiktok_url)
                            <a href="{{ $organization->tiktok_url }}" target="_blank" rel="noreferrer"
                                aria-label="TikTok {{ $organization->name }}"><svg viewBox="0 0 24 24">
                                    <path d="M14.5 4v9.2a3.7 3.7 0 1 1-3.2-3.66"></path>
                                    <path d="M14.5 4c.4 2.3 1.8 3.9 4.5 4.1"></path>
                                </svg></a>
                        @endif
                        @if ($organization->youtube_url)
                            <a href="{{ $organization->youtube_url }}" target="_blank" rel="noreferrer"
                                aria-label="YouTube {{ $organization->name }}"><svg viewBox="0 0 24 24">
                                    <rect x="2.5" y="5.5" width="19" height="13" rx="4"></rect>
                                    <path d="m10.5 9.5 5 2.5-5 2.5z" fill="currentColor" stroke="none"></path>
                                </svg></a>
                        @endif
                        @if ($organization->whatsapp_url)
                            <a href="{{ $organization->whatsapp_url }}" target="_blank" rel="noreferrer"
                                aria-label="WhatsApp {{ $organization->name }}"><svg viewBox="0 0 24 24">
                                    <path d="M12 3a9 9 0 0 0-7.8 13.5L3 21l4.6-1.2A9 9 0 1 0 12 3Z"></path>
                                    <path
                                        d="M8.5 10.5c.3 2.6 2.4 4.7 5 5l1-1.5-2-1-1 .5c-.7-.4-1.1-.8-1.5-1.5l.5-1-1-2-1 .5Z">
                                    </path>
                                </svg></a>
                        @endif
                        @if ($organization->facebook_url)
                            <a href="{{ $organization->facebook_url }}" target="_blank" rel="noreferrer"
                                aria-label="Facebook {{ $organization->name }}"><svg viewBox="0 0 24 24">
                                    <path
                                        d="M14 8h2.5V4.5H14A4.5 4.5 0 0 0 9.5 9v2.5H7V15h2.5v6H13v-6h2.5l.5-3.5h-3V9a1 1 0 0 1 1-1Z">
                                    </path>
                                </svg></a>
                        @endif
                        @if ($organization->x_url)
                            <a href="{{ $organization->x_url }}" target="_blank" rel="noreferrer"
                                aria-label="X {{ $organization->name }}"><svg viewBox="0 0 24 24">
                                    <path d="M4 4l16 16M20 4L4 20"></path>
                                </svg></a>
                        @endif
                        @if ($organization->email)
                            <a href="mailto:{{ $organization->email }}"
                                aria-label="Email {{ $organization->name }}"><svg viewBox="0 0 24 24">
                                    <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                                    <path d="m3 7 9 6 9-6"></path>
                                </svg></a>
                        @endif
                        @if ($organization->telegram_url)
                            <a href="{{ $organization->telegram_url }}" target="_blank" rel="noreferrer"
                                aria-label="Telegram {{ $organization->name }}"><svg viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="9"></circle>
                                    <path d="m7.5 12 9-4-3.5 9-2-3.5L7.5 12Z"></path>
                                </svg></a>
                        @endif
                        @if (
                            !(
                                $organization->instagram_url ||
                                $organization->tiktok_url ||
                                $organization->youtube_url ||
                                $organization->whatsapp_url ||
                                $organization->facebook_url ||
                                $organization->x_url ||
                                $organization->email ||
                                $organization->telegram_url
                            ))
                            <span class="kontak-empty">Kontak belum tersedia</span>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </main>
    @include('partials.site-footer')
</body>

</html>
