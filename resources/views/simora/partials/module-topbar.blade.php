{{-- Topbar bersama modul ORGMATE / E-PROGRESS / SENTRA.
     Variabel: $topbarAction (route pencarian), $topbarPlaceholder, $topbarClass, $bellBadge --}}
<header class="orgmate-topbar {{ $topbarClass ?? '' }}">
    <button class="orgmate-icon-btn" id="orgmateToggle" type="button" aria-label="Toggle sidebar"><i data-lucide="menu" class="icon"></i></button>
    @isset($breadcrumb)
        <nav class="orgmate-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('panel') }}">Dashboard</a><span>›</span><strong>{{ $breadcrumb }}</strong>
        </nav>
    @endisset
    @isset($topbarAction)
        <form class="orgmate-search {{ $searchClass ?? '' }}" method="GET" action="{{ $topbarAction }}" role="search">
            <i data-lucide="search" class="icon"></i>
            <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ $topbarPlaceholder ?? 'Cari…' }}" aria-label="Pencarian">
        </form>
    @endisset
    <button class="orgmate-icon-btn simora-theme-toggle" type="button" aria-label="Aktifkan mode gelap" data-theme-toggle><i data-lucide="moon" class="icon" aria-hidden="true"></i></button>
    <button class="orgmate-icon-btn orgmate-bell" type="button" aria-label="Notifikasi" id="orgmateBell"><i data-lucide="bell" class="icon"></i><span class="orgmate-bell-dot">{{ $bellBadge ?? 3 }}</span></button>
    <div class="orgmate-profile" id="orgmateProfile">
        <button class="orgmate-profile-btn" type="button" id="orgmateProfileBtn" aria-haspopup="menu" aria-expanded="false">
            <span class="orgmate-avatar orgmate-avatar-sm">@if (auth()->user()->avatar_url)<img src="{{ auth()->user()->avatar_url }}" alt="Foto profil {{ auth()->user()->name }}">@else{{ strtoupper($initials ?? '') }}@endif</span>
            <span class="orgmate-profile-meta"><strong>{{ auth()->user()->name }}</strong><small>{{ ucfirst(auth()->user()->role) }}</small></span>
            <i data-lucide="chevron-down" class="icon"></i>
        </button>
        <div class="orgmate-profile-menu" id="orgmateProfileMenu" role="menu" hidden>
            <a href="{{ route('panel') }}" role="menuitem"><i data-lucide="layout-dashboard" class="icon"></i> Kembali ke SIMORA</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="orgmate-profile-logout"><i data-lucide="log-out" class="icon"></i> Logout</button></form>
        </div>
    </div>
</header>
