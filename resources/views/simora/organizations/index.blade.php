<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo-head')
    <title>Kelola Organisasi | SIMORA</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/SIMORA.png') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/simora/panel/panel.css', 'resources/css/simora/organizations/organization-admin.css'])
</head>

<body class="panel-page">
    <x-panel-header active="organisasi" />
    <main class="panel-main org-admin-main">
        <div class="org-admin-heading">
            <div>
                <p class="panel-kicker">PENGELOLAAN LANDING PAGE</p>
                <h1>Organisasi</h1>
                <p>Kelola profil, identitas warna, dan logo organisasi Fakultas Teknik.</p>
            </div><a class="admin-primary" href="{{ route('simora.organizations.create') }}"><span>＋</span> Tambah
                Organisasi</a>
        </div>
        @if (session('status'))
            <div class="admin-alert">{{ session('status') }}</div>
        @endif
        @if ($organizations->isEmpty())
            <div class="admin-empty"><span>♧</span>
                <h2>Belum ada organisasi</h2>
                <p>Tambahkan organisasi agar tampil di landing page.</p><a class="admin-primary"
                    href="{{ route('simora.organizations.create') }}">Tambah Organisasi</a>
            </div>
        @else
            <div class="admin-org-grid">
                @foreach ($organizations as $organization)
                    <article class="admin-org-card"
                        style="--org-color: {{ $organization->banner_color }}; --org-accent: {{ $organization->accent_color }}">
                        <div class="admin-org-band"></div>
                        <img class="admin-org-logo" src="{{ $organization->logo_url }}"
                            alt="Logo {{ $organization->name }}">
                        <div class="admin-org-content"><span class="admin-org-focus">{{ $organization->focus }}</span>
                            <h2>{{ $organization->name }}</h2>
                            <p class="admin-org-fullname">{{ $organization->full_name }}</p>
                            @if ($organization->period)
                                <p class="admin-org-period">Periode · {{ $organization->period }}</p>
                            @endif
                            <p class="admin-org-description">{{ $organization->description }}</p>
                        </div>
                        <div class="admin-org-actions"><a class="admin-secondary"
                                href="{{ route('simora.organizations.edit', $organization) }}">Edit</a>
                            <form method="POST" action="{{ route('simora.organizations.destroy', $organization) }}"
                                onsubmit="return confirm('Hapus organisasi {{ $organization->name }}?')">@csrf
                                @method('DELETE')<button class="admin-danger" type="submit">Hapus</button></form>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="admin-pagination">{{ $organizations->links() }}</div>
        @endif
    </main>
</body>

</html>
