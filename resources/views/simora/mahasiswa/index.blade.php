@extends('simora.ORGMATE.layout', ['title' => 'Data Mahasiswa per Prodi | ORGMATE'])
@section('breadcrumb', 'Data Mahasiswa per Prodi')

@section('content')
    <div class="orgmate-intro">
        <div>
            <p class="orgmate-kicker">Data SSO UNUJA (Developer)</p>
            <h1>Data Mahasiswa per Prodi</h1>
            <p style="color:#475569;margin:6px 0 0">Data mahasiswa dari SSO UNUJA, dikelompokkan per prodi. Bukan data anggota organisasi — data anggota tetap dikelola lewat menu Data Anggota.</p>
        </div>
    </div>

    @if ($search !== '' || $prodi !== '')
        <div class="orgmate-card" style="padding:12px 16px;margin-bottom:12px">Menampilkan
            @if($prodi !== '') prodi <strong>{{ $prodi }}</strong>@endif
            @if($search !== '') pencarian <strong>"{{ $search }}"</strong>@endif
            <a href="{{ route('simora.mahasiswa') }}" style="color:#2563EB;font-weight:700">Bersihkan filter</a>
        </div>
    @endif

    <section class="orgmate-card" aria-label="Filter prodi" style="margin-bottom:14px">
        <form method="GET" action="{{ route('simora.mahasiswa') }}" class="orgmate-form">
            <div class="orgmate-form-grid" style="grid-template-columns:1fr 1fr auto;align-items:end">
                <div class="orgmate-field"><label for="mhsProdi">Program studi</label>
                    <select id="mhsProdi" name="prodi">
                        <option value="">— Semua prodi ({{ $counts->sum() }} mahasiswa) —</option>
                        @foreach ($prodiOptions as $option)
                            <option value="{{ $option }}" @selected($prodi === $option)>{{ $option }} ({{ $counts[$option] ?? 0 }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="orgmate-field"><label for="mhsSearch">Cari nama / NIM</label>
                    <input id="mhsSearch" name="q" value="{{ $search }}" placeholder="Nama atau NIM…">
                </div>
                <button type="submit" class="orgmate-btn">Terapkan</button>
            </div>
        </form>
    </section>

    <div class="orgmate-card">
        <div class="orgmate-table-wrap">
            <table class="orgmate-table">
                <thead>
                    <tr>
                        <th>Mahasiswa</th>
                        <th>NIM</th>
                        <th>Program Studi</th>
                        <th>Fakultas</th>
                        <th>Tahun Masuk</th>
                        <th>Sinkronisasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mahasiswas as $mhs)
                        <tr>
                            <td><strong>{{ $mhs->name }}</strong></td>
                            <td>{{ $mhs->nim ?? '—' }}</td>
                            <td>{{ $mhs->study_program ?? '—' }}</td>
                            <td>{{ $mhs->faculty ?? '—' }}</td>
                            <td>{{ $mhs->enrollment_year ?? '—' }}</td>
                            <td>{{ $mhs->synced_at?->locale('id')->translatedFormat('d M Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="orgmate-empty">Belum ada data mahasiswa. Jalankan <code>php artisan sso:sync-mahasiswa</code> untuk menarik dari SSO.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="orgmate-pagination-wrap">
        @if ($mahasiswas->total() > 0)
            <p class="orgmate-pagination-info">Menampilkan {{ $mahasiswas->firstItem() }}–{{ $mahasiswas->lastItem() }} dari {{ $mahasiswas->total() }} mahasiswa</p>
        @endif
        {{ $mahasiswas->links('vendor.pagination.orgmate') }}
    </div>
@endsection
