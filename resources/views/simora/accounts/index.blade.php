<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Akun | SIMORA</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/SIMORA.png') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="/assets/css/panel.css?v=5">
    <link rel="stylesheet" href="/assets/css/organization-admin.css?v=2">
    <link rel="stylesheet" href="/assets/css/account-admin.css?v=2">
</head>

<body class="panel-page">
    <x-panel-header active="akun" />
    <main class="panel-main org-admin-main">
        <div class="org-admin-heading">
            <div>
                <p class="panel-kicker">PENGELOLAAN SIMORA</p>
                <h1>Akun</h1>
                <p>Kelola akun developer dan organisasi seta kaitkan dengan organisasi Fakultas Teknik.</p>
            </div><a class="admin-primary" href="{{ route('simora.accounts.create') }}"><span>＋</span> Tambah Akun</a>
        </div>
        @if (session('status'))
            <div class="admin-alert">{{ session('status') }}</div>
        @endif
        <div class="account-table-wrap">
            <table class="account-table">
                <thead>
                    <tr>
                        <th>Akun</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Organisasi</th>
                        <th style="text-align:right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $account)
                        <tr>
                            <td class="account-user"><span
                                    class="account-avatar">@if ($account->avatar_url)<img src="{{ $account->avatar_url }}"
                                            alt="Foto {{ $account->name }}">@else{{ strtoupper(substr($account->name, 0, 2)) }}@endif</span><span>{{ $account->name }}
                                    @if ($account->is(auth()->user()))
                                        <small> · Kamu</small>
                                    @endif
                                </span></td>
                            <td>{{ $account->email }}</td>
                            <td><span class="role-badge role-{{ $account->role }}">{{ ucfirst($account->role) }}</span>
                            </td>
                            <td>
                                @if ($account->organization)
                                    <span class="org-cell"><img src="{{ $account->organization->logo_url }}"
                                            alt="">{{ $account->organization->name }}</span>
                                    <small class="org-full">{{ $account->organization->full_name }}</small>
                                @else
                                    <span class="org-none">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="account-actions">
                                    <a class="admin-secondary"
                                        href="{{ route('simora.accounts.edit', $account) }}">Edit</a>
                                    @if (!$account->is(auth()->user()))
                                        <form method="POST" action="{{ route('simora.accounts.destroy', $account) }}"
                                            onsubmit="return confirm('Hapus akun {{ $account->email }}?')">@csrf
                                            @method('DELETE')<button class="admin-danger" type="submit">Hapus</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="account-empty">Belum ada akun.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="admin-pagination">{{ $users->links() }}</div>
    </main>
</body>

</html>
