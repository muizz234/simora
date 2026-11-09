<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo-head')
    <title>Login SIMORA | Fakultas Teknik UNUJA</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/SIMORA.png') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/auth/auth.css', 'resources/js/auth/auth.js'])
</head>

<body class="login-page">
    <video id="loginBackgroundVideo" class="login-background-video" hidden preload="metadata" playsinline
        aria-label="Video latar belakang login">
        <source src="{{ asset('assets/login-background.mp4') }}" type="video/mp4">
    </video>
    <main class="login-layout">
        <section class="login-card" aria-labelledby="login-title">
            <div class="login-logos">
                <img src="{{ asset('assets/SIMORA.png') }}" alt="Logo SIMORA" class="simora-logo">
            </div>
            <h1 id="login-title" class="visually-hidden">Login SIMORA</h1>
            <form method="POST" action="{{ route('login.store') }}" class="login-form">
                @csrf
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username"
                    required autofocus>
                @error('email')
                    <p class="login-error">{{ $message }}</p>
                @enderror

                <label for="password">Password</label>
                <div class="password-field">
                    <input id="password" name="password" type="password" autocomplete="current-password" required>
                    <button type="button" class="password-toggle" data-password-toggle aria-label="Tampilkan password"
                        aria-pressed="false">
                        <svg class="eye-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg class="eye-off-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"></path>
                            <path
                                d="M9.9 5.2A10.8 10.8 0 0 1 12 5c6.4 0 10 7 10 7a16 16 0 0 1-3.2 4.1M6.2 6.2C3.5 8.1 2 12 2 12s3.6 7 10 7a10.7 10.7 0 0 0 3.1-.5">
                            </path>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="login-error">{{ $message }}</p>
                @enderror

                <label class="remember-option"><input type="checkbox" name="remember" value="1"><span>Ingat
                        saya</span></label>
                <button type="submit"><svg viewBox="0 0 24 24" aria-hidden="true" class="login-btn-icon">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                        <path d="m10 17 5-5-5-5M15 12H3"></path>
                    </svg> Login</button>
                <a class="login-back" href="{{ url('/') }}"><svg viewBox="0 0 24 24" aria-hidden="true"
                        class="login-btn-icon">
                        <path d="M19 12H5"></path>
                        <path d="m12 19-7-7 7-7"></path>
                    </svg> Kembali ke landing</a>
            </form>
        </section>
    </main>
    <button type="button" id="playBackgroundBtn" class="play-background-btn" aria-controls="loginBackgroundVideo">
        <svg viewBox="0 0 24 24" aria-hidden="true" class="login-btn-icon">
            <path d="m8 5 11 7-11 7V5Z"></path>
        </svg>
        <span>Play Background</span>
    </button>
</body>

</html>
