<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f5f7fb">
    <title>Kalender Kerja | SIMORA</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/SIMORA.png') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="/assets/css/panel.css?v=5">
    <link rel="stylesheet" href="/assets/css/module.css?v=2">
</head>

<body class="panel-page module-page"
    style="--module-color: {{ auth()->user()->organization?->banner_color ?? '#315eaa' }}">
    <x-panel-header active="kalender" />
    <main class="module-main">
        <a class="module-back" href="{{ route('panel') }}">← Kembali ke Dashboard</a>
        <section class="module-welcome">
            <span class="module-mark">▤</span>
            <p>SIMORA ·
                {{ auth()->user()->organization ? 'ORGANISASI · ' . strtoupper(auth()->user()->organization->name) : 'ORGANIZATION WORKSPACE' }}
            </p>
            <h1>Kalender Kerja</h1>
            <span class="module-user">Organization: {{ auth()->user()->name }}</span>
        </section>
        <section class="module-empty" aria-label="Area Kalender Kerja">
            <span class="empty-symbol">▤</span>
            <h2>Kalender Kerja</h2>
            <p>Area ini siap dikembangkan.</p>
        </section>
    </main>
</body>

</html>
