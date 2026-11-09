<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo-head')
    <meta name="theme-color" content="#f5f8ff">
    <title>{{ $activity->name }} | Kegiatan Fakultas Teknik UNUJA</title>
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
    <main class="kegiatan-detail-main section-wrap">
        <a class="kegiatan-back" href="{{ route('kegiatan.index') }}">← Semua Kegiatan</a>

        @php
            $photos = $activity->documentations->where(fn ($doc) => $doc->is_image);
            $others = $activity->documentations->reject(fn ($doc) => $doc->is_image);
            $cover = $photos->first();
        @endphp

        <div class="kegiatan-detail-hero">
            @if ($cover && $cover->file_url)
                <div class="kegiatan-detail-cover">
                    <img src="{{ $cover->file_url }}" alt="Dokumentasi {{ $activity->name }}">
                </div>
            @endif
            <div class="kegiatan-detail-info">
                <div class="activity-org">
                    @if ($activity->organization?->logo_url)
                        <img src="{{ $activity->organization->logo_url }}" alt="Logo {{ $activity->organization->name }}">
                    @endif
                    <span>Diselenggarakan oleh {{ $activity->organization?->name ?? '—' }}</span>
                </div>
                <h1>{{ $activity->name }}</h1>
                <div class="kegiatan-detail-meta">
                    @if($activity->start_at)
                        <span>📅 {{ $activity->start_at->locale('id')->translatedFormat('l, d F Y') }} · {{ $activity->start_at->format('H:i') }} WIB</span>
                    @else
                        <span>📅 —</span>
                    @endif
                    @if($activity->location)<span>📍 {{ $activity->location }}</span>@endif
                    @if($activity->workProgram)<span>🧩 Proker: {{ $activity->workProgram->name }}</span>@endif
                    <span class="activity-status">{{ ucfirst($activity->status) }}</span>
                </div>
                @if ($activity->description)
                    <p class="kegiatan-detail-desc">{{ $activity->description }}</p>
                @endif
            </div>
        </div>

        <h2 class="kegiatan-detail-title">Galeri Dokumentasi <span>({{ $activity->documentations->count() }} berkas)</span></h2>

        @if ($photos->isNotEmpty())
            <div class="kegiatan-gallery">
                @foreach ($photos as $photo)
                    <a href="{{ $photo->file_url }}" target="_blank" rel="noopener" class="kegiatan-gallery-item">
                        <img src="{{ $photo->file_url }}" alt="{{ $photo->title }}" loading="lazy">
                        <span>{{ $photo->title }}</span>
                    </a>
                @endforeach
            </div>
        @endif

        @if ($others->isNotEmpty())
            <div class="kegiatan-files">
                @foreach ($others as $file)
                    <a href="{{ $file->file_url }}" target="_blank" rel="noopener" class="kegiatan-file">
                        <span>{{ $file->type === 'video' ? '▶' : '📄' }}</span>
                        <span><strong>{{ $file->title }}</strong><small>{{ ucfirst($file->type) }} · klik untuk membuka</small></span>
                    </a>
                @endforeach
            </div>
        @endif

        @if ($related->isNotEmpty())
            <h2 class="kegiatan-detail-title">Kegiatan Lain dari {{ $activity->organization?->name }}</h2>
            <div class="activity-grid">
                @foreach ($related as $other)
                    @php $otherCover = $other->documentations->first(fn ($doc) => $doc->is_image); @endphp
                    <article class="activity-card">
                        @if ($otherCover && $otherCover->file_url)
                            <a href="{{ $otherCover->file_url }}" target="_blank" rel="noopener" class="activity-cover">
                                <img src="{{ $otherCover->file_url }}" alt="Dokumentasi {{ $other->name }}" loading="lazy">
                                @if ($other->workProgram)<span class="activity-proker">{{ $other->workProgram->name }}</span>@endif
                                <span class="activity-doc-count">✦ {{ $other->documentations->count() }} dokumentasi</span>
                            </a>
                        @endif
                        <div class="activity-body">
                            <div class="activity-org">
                                @if ($other->organization?->logo_url)
                                    <img src="{{ $other->organization->logo_url }}" alt="Logo {{ $other->organization->name }}" loading="lazy">
                                @endif
                                <span>{{ $other->organization?->name ?? '—' }}</span>
                            </div>
                            <h3>{{ $other->name }}</h3>
                            <p class="activity-meta">{{ $other->start_at?->locale('id')->translatedFormat('l, d F Y') ?? '—' }}</p>
                            <a class="activity-more" href="{{ route('kegiatan.show', $other) }}">Lihat Selengkapnya <span>↗</span></a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </main>
    @include('partials.site-footer')
</body>

</html>
