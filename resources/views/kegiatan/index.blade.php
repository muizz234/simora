<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo-head')
    <meta name="theme-color" content="#f5f8ff">
    <title>Kegiatan &amp; Dokumentasi | Fakultas Teknik UNUJA</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/Logo FT.png') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    @vite(['resources/css/landing/landing.css', 'resources/js/landing/landing.js', 'resources/js/clock.js'])
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
            <a class="nav-link" href="{{ route('struktur.index') }}">Struktural</a>
            <a class="nav-link active" href="{{ route('kegiatan.index') }}" aria-current="page">Kegiatan</a>
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
    <main class="kegiatan-main">
        <section class="activities section-wrap">
            <div class="section-heading">
                <div>
                    <p class="section-kicker">DOKUMENTASI KEGIATAN</p>
                    <h2>Kabar <span>kegiatan kami.</span></h2>
                </div>
            </div>
            @if ($activities->isNotEmpty())
                <div class="activity-grid">
                    @foreach ($activities as $activity)
                        @php $cover = $activity->documentations->first(fn ($doc) => $doc->is_image); @endphp
                        <article class="activity-card">
                            @if ($cover && $cover->file_url)
                                <a href="{{ $cover->file_url }}" target="_blank" rel="noopener" class="activity-cover">
                                    <img src="{{ $cover->file_url }}" alt="Dokumentasi {{ $activity->name }}" loading="lazy">
                                    @if ($activity->workProgram)
                                        <span class="activity-proker">{{ $activity->workProgram->name }}</span>
                                    @endif
                                    <span class="activity-doc-count">✦ {{ $activity->documentations->count() }} dokumentasi</span>
                                </a>
                            @else
                                <div class="activity-cover activity-cover--placeholder">
                                    @if ($activity->workProgram)
                                        <span class="activity-proker">{{ $activity->workProgram->name }}</span>
                                    @endif
                                    <span class="activity-doc-count">✦ {{ $activity->documentations->count() }} dokumentasi</span>
                                </div>
                            @endif
                            <div class="activity-body">
                                <div class="activity-org">
                                    @if ($activity->organization?->logo_url)
                                        <img src="{{ $activity->organization->logo_url }}" alt="Logo {{ $activity->organization->name }}" loading="lazy">
                                    @endif
                                    <span>{{ $activity->organization?->name ?? '—' }}</span>
                                </div>
                                <h3>{{ $activity->name }}</h3>
                                <p class="activity-meta">
                                    {{ $activity->start_at?->locale('id')->translatedFormat('l, d F Y') ?? '—' }}
                                    @if ($activity->start_at)<span> · {{ $activity->start_at->format('H:i') }} WIB</span>@endif
                                    @if ($activity->location)<span> · {{ $activity->location }}</span>@endif
                                </p>
                                @if ($activity->description)
                                    <p class="activity-desc">{{ \Illuminate\Support\Str::limit($activity->description, 110) }}</p>
                                @endif
                                <span class="activity-status">{{ ucfirst($activity->status) }}</span>
                                <a class="activity-more" href="{{ route('kegiatan.show', $activity) }}">Lihat Selengkapnya <span>↗</span></a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="activity-empty">
                    <p>Belum ada dokumentasi kegiatan. Kabar kegiatan akan tampil di sini begitu organisasi mengunggah dokumentasi dari E-PROGRESS.</p>
                </div>
            @endif
        </section>
    </main>
    @include('partials.site-footer')
</body>

</html>
