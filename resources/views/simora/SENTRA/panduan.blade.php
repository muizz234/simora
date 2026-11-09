@extends('simora.SENTRA.layout', ['title' => 'Panduan SENTRA'])

@section('content')
    <div class="sentra-head">
        <div>
            <h1>Panduan SENTRA</h1>
            <p>Cara memakai setiap menu SENTRA untuk mengelola keuangan organisasi.</p>
        </div>
        <a class="orgmate-btn ghost" href="{{ route('simora.sentra') }}">← Kembali ke Dashboard</a>
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
            <li>Alur yang disarankan: Anggaran &amp; RAB → Kas/Iuran → Realisasi → Laporan Keuangan.</li>
            <li>SEMUA transaksi uang (kas, iuran, realisasi) WAJIB melampirkan bukti — klik 📷 Kamera untuk foto struk langsung atau upload file.</li>
            <li>Iuran yang lunas dan pengeluaran realisasi otomatis tercatat di Kas; uang tidak perlu dicatat dua kali.</li>
            <li>Akun organisasi hanya bisa menulis data organisasinya sendiri; developer hanya memonitor lintas organisasi.</li>
        </ul>
    </div>
@endsection
