@extends('simora.E-PROGRESS.layout', ['title' => 'Panduan E-PROGRESS'])

@section('content')
    <div class="orgmate-intro">
        <div>
            <p class="orgmate-kicker">Panduan Penggunaan · Scope: {{ $orgName ?? 'Semua Organisasi' }}</p>
            <h1>Panduan E-PROGRESS</h1>
            <p class="lead">Cara memakai setiap menu E-Progress untuk mengelola kegiatan dan proker.</p>
        </div>
        <div class="orgmate-intro-actions">
            <a class="orgmate-back" href="{{ route('simora.e-progress') }}">← Kembali ke Dashboard</a>
        </div>
    </div>

    <div class="guide-grid">
        @foreach ($guides as $guide)
            <article class="orgmate-card guide-card">
                <div class="orgmate-card-head"><h2><i data-lucide="{{ $guide['icon'] }}"></i> {{ $guide['title'] }}</h2></div>
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
        <div class="orgmate-card-head"><h2><i data-lucide="lightbulb"></i> Tips Umum</h2></div>
        <ul class="guide-tips-list">
            <li>Alur yang disarankan: Proker → Kalender Kegiatan → Kepanitiaan → QR Presensi → Dokumentasi → Evaluasi/LPJ.</li>
            <li>Akun organisasi hanya bisa menulis data organisasinya sendiri; developer hanya memonitor lewat pemilih organisasi.</li>
            <li>Mengunggah dokumentasi membuat kegiatan tampil di halaman publik Kegiatan (landing).</li>
            <li>Gunakan tombol ☾/☀ di kanan atas untuk mode gelap/terang; tampilan responsif di semua ukuran layar.</li>
        </ul>
    </div>
@endsection
