<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#092e69">
    <title>Presensi {{ $activity->name }} | E-PROGRESS SIMORA</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/SIMORA.png') }}">
    <link rel="stylesheet" href="/assets/css/e-progress.css?v=3">
    <link rel="stylesheet" href="/assets/css/simora-theme.css?v=2">
</head>

<body class="orgmate-body eprogress-body">
    <div class="eprogress-checkin-wrap">
        <div class="orgmate-card eprogress-checkin-card">
            <img src="{{ asset('assets/e-progress-logo.png') }}" alt="Logo E-PROGRESS" class="eprogress-checkin-logo">
            <p class="orgmate-kicker">Presensi Kegiatan</p>
            <h1>{{ $activity->name }}</h1>
            <p class="orgmate-hint">
                {{ $activity->start_at?->locale('id')->translatedFormat('l, d F Y · H:i') ?? '' }}
                @if($activity->location) · {{ $activity->location }}@endif
            </p>

            @if ($checkedIn)
                <div class="eprogress-checkin-success">
                    <span class="eprogress-checkin-icon"><i data-lucide="check"></i></span>
                    <h2>Berhasil Check-in!</h2>
                    <p>Nama Anda sudah tercatat. Terima kasih sudah hadir.</p>
                </div>
            @else
                <form method="POST" action="{{ request()->fullUrl() }}" class="orgmate-form eprogress-checkin-form">
                    @csrf
                    <div class="orgmate-field">
                        <label for="checkinName">Nama lengkap</label>
                        <input id="checkinName" name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="Ketuk untuk menulis nama">
                        @error('name')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <button type="submit" class="orgmate-btn eprogress-checkin-btn">Saya Hadir — Check-in</button>
                </form>
            @endif
        </div>
    </div>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>if (window.lucide) window.lucide.createIcons();</script>
</body>

</html>
