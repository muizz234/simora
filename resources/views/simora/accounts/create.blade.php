<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo-head')
    <title>Tambah Akun | SIMORA</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/SIMORA.png') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/simora/panel/panel.css', 'resources/css/simora/organizations/organization-admin.css'])
</head>

<body class="panel-page">
    <x-panel-header active="akun" />
    <main class="org-form-main"><a class="admin-header-back" style="display:inline-block;margin-bottom:16px"
            href="{{ route('simora.accounts.index') }}">← Kembali ke Akun</a>
        <div class="org-form-heading">
            <p class="panel-kicker">PENGELOLAAN SIMORA</p>
            <h1>Tambah Akun</h1>
            <p>Buat akun developer atau organisasi dan kaitkan dengan organisasi.</p>
        </div>
        <form class="org-form" method="POST" action="{{ route('simora.accounts.store') }}" enctype="multipart/form-data">@csrf
            @include('simora.accounts._form-fields', [
                'account' => null,
                'organizations' => $organizations,
            ])<div class="org-form-actions"><a class="admin-secondary"
                    href="{{ route('simora.accounts.index') }}">Batal</a><button type="submit"
                    class="admin-primary">Simpan Akun</button></div>
        </form>
    </main>
</body>

</html>
