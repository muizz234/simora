@extends('simora.ORGMATE.layout', ['title' => 'Panduan ORGMATE'])

@section('breadcrumb', 'Panduan ORGMATE')

@section('content')
    <div class="orgmate-intro">
        <div>
            <p class="orgmate-kicker">Panduan Penggunaan · Scope: {{ $orgName ?? 'Semua Organisasi' }}</p>
            <h1>Panduan ORGMATE</h1>
            <p class="lead">Cara memakai setiap menu ORGMATE untuk mengelola organisasi Anda.</p>
        </div>
        <div class="orgmate-intro-actions">
            <a class="orgmate-back" href="{{ route('simora.orgmate') }}">← Kembali ke Dashboard</a>
        </div>
    </div>

    <div class="guide-grid">
        @foreach ($guides as $guide)
            <article class="orgmate-card guide-card">
                <div class="orgmate-card-head"><h2><i data-lucide="{{ $guide['icon'] }}" class="icon"></i> {{ $guide['title'] }}</h2></div>
                <p class="guide-desc">{{ $guide['desc'] }}</p>
                <ol class="guide-steps">
                    @foreach ($guide['steps'] as $step)
                        <li>{{ $step }}</li>
                    @endforeach
                </ol>
            </article>
        @endforeach
    </div>

    <div class="orgmate-card guide-tips">
        <div class="orgmate-card-head"><h2><i data-lucide="lightbulb" class="icon"></i> Tips Umum</h2></div>
        <ul class="guide-tips-list">
            <li>Akun organisasi hanya bisa menulis data organisasinya sendiri; akun developer hanya melihat/memonitor lintas organisasi.</li>
            <li>Anggota adalah data keanggotaan (mahasiswa), bukan akun login — jabatan menunjuk ke anggota.</li>
            <li>Logo organisasi adalah foto profil akun organisasi; tampil di semua halaman dan landing publik.</li>
            <li>Gunakan tombol ☾/☀ di kanan atas untuk mode gelap/terang; tampilan responsif di semua ukuran layar.</li>
        </ul>
    </div>
@endsection
