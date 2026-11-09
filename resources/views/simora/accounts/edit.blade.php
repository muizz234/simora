<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit {{ $account->name }} | SIMORA</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/SIMORA.png') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}"><link rel="stylesheet" href="/assets/css/panel.css?v=5"><link rel="stylesheet" href="/assets/css/organization-admin.css?v=2">
</head>

<body class="panel-page">
    <x-panel-header active="akun" />
    <main class="org-form-main"><a class="admin-header-back" style="display:inline-block;margin-bottom:16px"
            href="{{ route('simora.accounts.index') }}">← Kembali ke Akun</a>
        <div class="org-form-heading">
            <p class="panel-kicker">PENGELOLAAN SIMORA</p>
            <h1>Edit Akun</h1>
            <p>Perbarui data, role, organisasi, atau reset password akun.</p>
        </div>
        <form class="org-form" method="POST" action="{{ route('simora.accounts.update', $account) }}" enctype="multipart/form-data">@csrf
            @method('PUT') @include('simora.accounts._form-fields', [
                'account' => $account,
                'organizations' => $organizations,
            ])<div class="org-form-actions"><a class="admin-secondary"
                    href="{{ route('simora.accounts.index') }}">Batal</a><button type="submit"
                    class="admin-primary">Simpan Perubahan</button></div>
        </form>
    </main>
</body>

</html>
