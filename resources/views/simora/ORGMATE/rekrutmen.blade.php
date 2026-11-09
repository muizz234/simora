@extends('simora.ORGMATE.layout', ['title' => 'Rekrutmen | ORGMATE'])
@section('breadcrumb', 'Rekrutmen Anggota')

@section('content')
    <div class="orgmate-intro">
        <div>
            <p class="orgmate-kicker">Rekrutmen Anggota</p>
            <h1>Rekrutmen &amp; Pendaftaran Anggota</h1>
            <p class="lead">Link pendaftaran open recruitment setiap organisasi (tampil di landing).</p>
        </div><a class="orgmate-back" href="{{ route('simora.orgmate') }}">← Kembali</a>
    </div>
    <div class="orgmate-org-grid">
        @forelse($organizations as $organization)
            <article class="orgmate-org-card" style="--org-color: {{ $organization->banner_color }}"><img
                    src="{{ $organization->logo_url ?? asset('assets/Logo FT.png') }}" alt="Logo {{ $organization->name }}">
                <div><strong>{{ $organization->name }}</strong><span>{{ $organization->full_name }}</span>
                    @if ($organization->registration_url)
                        <a href="{{ $organization->registration_url }}" target="_blank" rel="noreferrer">Link Pendaftaran
                        ↗</a>@else<small>Link belum tersedia</small>
                    @endif
                    <small>Periode: {{ $organization->period ?? '—' }}</small>
                </div>
            </article>
        @empty
            <div class="orgmate-empty">Belum ada data organisasi.</div>
        @endforelse
    </div>
    <div class="orgmate-card">
        <div class="orgmate-card-head">
            <div>
                <h2>Ringkasan Rekrutmen</h2>
                <p>Sistem seleksi akan tampil di sini setelah tabel rekrutmen tersedia.</p>
            </div>
        </div>
        <div class="orgmate-kv">
            <div><span>Pendaftar baru</span><strong>0</strong></div>
            <div><span>Diterima</span><strong>0</strong></div>
            <div><span>Dalam seleksi</span><strong>0</strong></div>
            <div><span>Ditolak</span><strong>0</strong></div>
        </div>
    </div>
@endsection
