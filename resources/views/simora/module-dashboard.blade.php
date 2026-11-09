<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f5f7fb">
    <title>{{ $moduleName }} | {{ auth()->user()->role === 'organization' ? 'SIMORA Aplikasi' : 'SIMORA Workspace' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/SIMORA.png') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="/assets/css/panel.css?v=5">
    <link rel="stylesheet" href="/assets/css/module.css?v=2">
</head>
<body class="panel-page module-page" style="--module-color: {{ $moduleColor }}">
    <x-panel-header :module-name="$moduleName" :module-number="$moduleNumber" />
    <main class="module-main">
        <a class="module-back" href="{{ route('panel') }}">← Kembali ke Dashboard</a>
        <section class="module-welcome">
            <span class="module-mark">{{ substr($moduleName, 0, 1) }}</span>
            <p>{{ auth()->user()->role === 'organization' ? 'APLIKASI MANAGEMENT ORGANISASI · APLIKASI ' . $moduleNumber : 'SIMORA DEVELOPER WORKSPACE · MODUL ' . $moduleNumber }}</p>
            <h1>Dashboard {{ $moduleName }}</h1>
            <span class="module-user">{{ ucfirst(auth()->user()->role) }}: {{ auth()->user()->name }}</span>
        </section>
        <section class="module-empty" aria-label="Area kerja {{ $moduleName }}">
            <span class="empty-symbol">✳</span>
            <h2>{{ auth()->user()->role === 'organization' ? 'Aplikasi' : 'Workspace' }} {{ $moduleName }}</h2>
            <p>Area ini siap dikembangkan.</p>
        </section>
    </main>
</body>
</html>
