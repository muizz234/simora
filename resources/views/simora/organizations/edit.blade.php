<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit {{ $organization->name }} | SIMORA</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/SIMORA.png') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}"><link rel="stylesheet" href="/assets/css/panel.css?v=5"><link rel="stylesheet" href="/assets/css/organization-admin.css?v=2">
</head>

<body class="panel-page">
    <x-panel-header active="organisasi" />
    <main class="org-form-main"><a class="admin-header-back" style="display:inline-block;margin-bottom:16px"
            href="{{ route('simora.organizations.index') }}">← Kembali ke Organisasi</a>
        <div class="org-form-heading">
            <p class="panel-kicker">PENGELOLAAN LANDING PAGE</p>
            <h1>Edit {{ $organization->name }}</h1>
            <p>Perbarui informasi dan identitas organisasi.</p>
        </div>
        <form class="org-form" method="POST" action="{{ route('simora.organizations.update', $organization) }}"
            enctype="multipart/form-data">@csrf @method('PUT') @include('simora.organizations._form-fields', ['organization' => $organization])<div
                class="org-form-actions"><a class="admin-secondary"
                    href="{{ route('simora.organizations.index') }}">Batal</a><button type="submit"
                    class="admin-primary">Simpan Perubahan</button></div>
        </form>
    </main>
</body>

</html>
