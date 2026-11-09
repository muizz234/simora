<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f5f8ff">
    <title>Struktur {{ $organization->name }} | Fakultas Teknik UNUJA</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/Logo FT.png') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/landing.css?v=3">
    <link rel="stylesheet" href="/assets/css/struktur.css?v=7">
    <script src="/assets/js/landing.js?v=2" defer></script>
    <script src="/assets/js/clock.js" defer></script>
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
    <main class="struktur-main"
        style="--org-color: {{ $organization->banner_color }}; --org-accent: {{ $organization->accent_color }}">
        <a class="struktur-back" href="{{ route('struktur.index') }}">← Semua Organisasi</a>
        <section class="struktur-hero">
            <img class="struktur-hero-logo" src="{{ $organization->logo_url }}" alt="Logo {{ $organization->name }}">
            <div>
                <p class="panel-kicker">ORGANISASI MAHASISWA · {{ strtoupper($organization->focus) }}</p>
                <h1>{{ $organization->name }}</h1>
                <p>{{ $organization->full_name }}</p>
                @if ($organization->period)
                    <span class="struktur-period">Periode · {{ $organization->period }}</span>
                @endif
            </div>
        </section>
        <p class="struktur-desc">{{ $organization->description }}</p>
        @php
            // Kelompokkan jabatan menurut tingkatannya untuk bagan struktural.
            $levelOf = fn ($position) => match ($position->group_type) {
                'pimpinan' => 1,
                'wakil_pimpinan' => 2,
                'bpi' => 3,
                'divisi' => 4,
                default => match (true) {
                    str_contains($position->name, 'Koordinator') => 4,
                    str_contains($position->name, 'Wakil') && str_contains($position->name, 'Ketua') => 2,
                    str_contains($position->name, 'Ketua Umum') => 1,
                    str_contains($position->name, 'Sekretaris') || str_contains($position->name, 'Bendahara') => 3,
                    default => 3,
                },
            };
            $levels = [1 => [], 2 => [], 3 => [], 4 => []];
            foreach ($positions as $position) {
                $levels[$levelOf($position)][] = $position;
            }
            $levelLabels = [1 => 'Pimpinan', 2 => 'Wakil Pimpinan', 3 => 'BPI (Badan Pengurus Inti)', 4 => 'Koordinator Divisi'];
        @endphp
        @if ($positions->isNotEmpty())
            <div class="struktur-orgchart" aria-label="Bagan struktur {{ $organization->name }}">
                @foreach ($levels as $level => $items)
                    @if (count($items) > 0)
                        <div class="struktur-chart-level struktur-chart-level--{{ $level }}">
                            <span class="struktur-chart-label">{{ $levelLabels[$level] }}</span>
                            <div class="struktur-chart-row">
                                @foreach ($items as $position)
                                    <div class="struktur-chart-node">
                                        <article class="struktur-official struktur-official--level-{{ $level }}">
                                            <div class="struktur-photo">
                                                @if ($position->member?->avatar_url)
                                                    <img src="{{ $position->member->avatar_url }}"
                                                        alt="Foto {{ $position->member->name }}" loading="lazy">
                                                @else
                                                    <span aria-hidden="true">＋</span>
                                                @endif
                                            </div>
                                            <strong>{{ $position->name }}</strong>
                                            <span>{{ $position->member ? $position->member->name : 'Pengurus ' . $organization->name }}</span>
                                        </article>
                                        @if ($level === 4)
                                            @php
                                                $divisionName = preg_replace('/^Koordinator Divisi\s+/i', '', $position->name);
                                                $divisionMembers = $members->filter(fn ($member) => ($member->division ?? '') !== ''
                                                    && strcasecmp($member->division, $divisionName) === 0
                                                    && $member->id !== $position->member_id);
                                            @endphp
                                            @if ($divisionMembers->isNotEmpty())
                                                <div class="struktur-division-members">
                                                    <span class="struktur-division-label">Anggota Divisi</span>
                                                    @foreach ($divisionMembers as $divisionMember)
                                                        <article class="struktur-official struktur-official--member">
                                                            <div class="struktur-photo struktur-photo--sm">
                                                                @if ($divisionMember->avatar_url)
                                                                    <img src="{{ $divisionMember->avatar_url }}"
                                                                        alt="Foto {{ $divisionMember->name }}" loading="lazy">
                                                                @else
                                                                    <span aria-hidden="true">＋</span>
                                                                @endif
                                                            </div>
                                                            <strong>{{ $divisionMember->name }}</strong>
                                                            <span>{{ $divisionMember->nim ?? 'Anggota Divisi' }}</span>
                                                        </article>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <p class="struktur-desc">Struktur kepengurusan belum diisi untuk organisasi ini.</p>
        @endif
        @if ($organization->vision || $organization->mission)
            <section class="struktur-vision">
                @if ($organization->vision)
                    <article>
                        <h2>Visi</h2>
                        <p>{{ $organization->vision }}</p>
                    </article>
                @endif
                @if ($organization->mission)
                    <article>
                        <h2>Misi</h2>
                        <p>{{ $organization->mission }}</p>
                    </article>
                @endif
            </section>
        @endif
        {{-- Daftar anggota flat dihapus: semua anggota tampil di bagan struktural di atas. --}}
    </main>
    @include('partials.site-footer')
</body>

</html>
