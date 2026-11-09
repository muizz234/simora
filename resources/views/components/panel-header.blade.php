@php
    $isDeveloper = auth()->user()?->role === 'developer';
    $isOrganization = auth()->user()?->role === 'organization';
    $active = $active ?? null;
    if ($active === null) {
        $active = match (true) {
            request()->routeIs('panel') => 'dashboard',
            request()->routeIs('simora.organizations.*') => 'organisasi',
            request()->routeIs('simora.accounts.*') => 'akun',
            request()->routeIs('simora.kalender-kerja') => 'kalender',
            request()->routeIs('simora.orgmate*', 'simora.e-progress', 'simora.sentra') => $isOrganization
                ? 'aplikasi'
                : 'workspace',
            default => null,
        };
    }
@endphp
<header class="panel-header">
    <a class="portal-brand" href="{{ route('panel') }}">
        <img src="{{ asset('assets/SIMORA.png') }}" alt="Logo SIMORA" class="portal-logo">
        <span><strong>SIMORA</strong><small>{{ $isOrganization ? 'Fakultas Teknik' : 'Workspace · Fakultas Teknik' }}</small></span>
    </a>
    <nav class="panel-nav" aria-label="Navigasi panel">
        <a class="{{ $active === 'dashboard' ? 'selected' : '' }}" href="{{ route('panel') }}"
            @if ($active === 'dashboard') aria-current="page" @endif><span>⌂</span> Dashboard</a>
        @if ($isDeveloper)
            <a class="{{ $active === 'organisasi' ? 'selected' : '' }}" href="{{ route('simora.organizations.index') }}"
                @if ($active === 'organisasi') aria-current="page" @endif><span>♧</span> Organisasi</a>
            <a class="{{ $active === 'akun' ? 'selected' : '' }}" href="{{ route('simora.accounts.index') }}"
                @if ($active === 'akun') aria-current="page" @endif><span>⚙</span> Akun</a>
            <a class="{{ $active === 'landing' ? 'selected' : '' }}" href="{{ route('simora.landing-management') }}"
                @if ($active === 'landing') aria-current="page" @endif><span>◐</span> Kelola Landing</a>
            <a class="{{ $active === 'workspace' ? 'selected' : '' }}" href="{{ route('panel') }}#workspace"
                @if ($active === 'workspace') aria-current="page" @endif><span>▦</span> Workspace</a>
        @endif
        @if ($isOrganization)
            <a class="{{ $active === 'kalender' ? 'selected' : '' }}" href="{{ route('simora.kalender-kerja') }}"
                @if ($active === 'kalender') aria-current="page" @endif><span>▤</span> Kalender Kerja</a>
        @endif
        @if (isset($moduleName) && isset($moduleNumber))
            <span class="module-header-label"><span>{{ $moduleNumber }}</span> {{ $moduleName }}</span>
        @endif
    </nav>
    <div class="panel-account">
        <span class="account-avatar">@if (auth()->user()->avatar_url)<img src="{{ auth()->user()->avatar_url }}"
                alt="Foto profil {{ auth()->user()->name }}">@else{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}@endif</span>
        <span class="account-name">{{ auth()->user()->name }}</span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-button" aria-label="Keluar" title="Keluar">↪</button>
        </form>
    </div>
</header>
