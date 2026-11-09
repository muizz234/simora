@extends('simora.E-PROGRESS.layout', ['title' => 'Absensi / Presensi | E-PROGRESS'])

@section('content')
    <div class="orgmate-intro">
        <div>
            <p class="orgmate-kicker">Absensi / Presensi (QR Code) · Scope: {{ $orgName ?? 'Semua Organisasi' }}</p>
            <h1>Absensi / Presensi</h1>
            <p class="lead">Peserta memindai QR code kegiatan lalu mengisi nama — tanpa perlu login{{ ($isDeveloper ?? false) ? ' (mode monitoring developer)' : '' }}.</p>
        </div>
        <div class="orgmate-intro-actions">
            @if (($isDeveloper ?? false) && ($organizationsList ?? collect())->count() > 0)
                <form method="GET" action="{{ route('simora.e-progress.presensi') }}" class="orgmate-org-form orgmate-org-form--inline">
                    <label for="presensiOrgSelect">Lihat organisasi:</label>
                    <select id="presensiOrgSelect" name="organization" onchange="this.form.submit()">
                        <option value="">Semua Organisasi</option>
                        @foreach ($organizationsList as $org)
                            <option value="{{ $org->slug }}" {{ $selectedOrganization?->slug === $org->slug ? 'selected' : '' }}>{{ $org->name }}</option>
                        @endforeach
                    </select>
                </form>
            @endif
            <a class="orgmate-back" href="{{ route('simora.e-progress') }}">← Kembali</a>
        </div>
    </div>

    @if (session('status'))
        <div class="orgmate-toast is-show" style="position:static;opacity:1;transform:none;margin-bottom:12px">{{ session('status') }}</div>
    @endif

    @if ($activities->isEmpty())
        <div class="orgmate-card">
            <div class="orgmate-empty">
                Belum ada kegiatan. Buat kegiatan dulu di
                <a href="{{ route('simora.e-progress.kalender') }}">Kalender Kegiatan</a>, lalu buat QR presensinya di sini.
            </div>
        </div>
    @else
        <div class="eprogress-stats eprogress-stats-3">
            <article class="eprogress-stat eprogress-stat--pastel-blue"><span class="eprogress-stat-icon"><i data-lucide="users"></i></span><div><span>Total Hadir</span><strong>{{ $stats['totalHadir'] }}</strong><small>Pada kegiatan terpilih</small></div></article>
            <article class="eprogress-stat eprogress-stat--pastel-green"><span class="eprogress-stat-icon"><i data-lucide="qr-code"></i></span><div><span>Via QR Code</span><strong>{{ $stats['viaQr'] }}</strong><small>Scan QR peserta</small></div></article>
            <article class="eprogress-stat eprogress-stat--pastel-amber"><span class="eprogress-stat-icon"><i data-lucide="pen-line"></i></span><div><span>Manual</span><strong>{{ $stats['manual'] }}</strong><small>Dicatat panitia</small></div></article>
        </div>

        <div class="orgmate-card">
            <div class="orgmate-card-head">
                <h2><i data-lucide="flag"></i> Pilih Kegiatan</h2>
                <form method="GET" action="{{ route('simora.e-progress.presensi') }}" class="orgmate-org-form orgmate-org-form--inline eprogress-panitia-select">
                    @if($selectedOrganization)<input type="hidden" name="organization" value="{{ $selectedOrganization->slug }}">@endif
                    <label for="activitySelect">Kegiatan:</label>
                    <select id="activitySelect" name="activity" onchange="this.form.submit()">
                        @foreach ($activities as $activity)
                            <option value="{{ $activity->id }}" {{ $selectedActivity?->id === $activity->id ? 'selected' : '' }}>{{ $activity->name }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        <div class="eprogress-presensi-grid">
            <div class="orgmate-card eprogress-qr-card" id="qrPrintArea">
                <div class="orgmate-card-head"><h2><i data-lucide="qr-code"></i> QR Presensi</h2></div>
                <p class="orgmate-hint">Tayangkan QR ini di pintu masuk. Peserta scan, lalu isi nama mereka.</p>
                @if ($selectedActivity && $checkinUrl)
                    <div class="eprogress-qr-box" id="qrBox">
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(260)->generate($checkinUrl) !!}
                    </div>
                    <p class="eprogress-qr-name"><strong>{{ $selectedActivity->name }}</strong></p>
                    <p class="eprogress-qr-date">{{ $selectedActivity->start_at?->locale('id')->translatedFormat('l, d F Y · H:i') ?? '' }}</p>
                    <p class="orgmate-field-hint">Tautan check-in (berlaku khusus kegiatan ini):</p>
                    <code class="eprogress-qr-url">{{ $checkinUrl }}</code>
                    <div class="eprogress-qr-actions">
                        <a class="orgmate-btn ghost orgmate-btn-sm" href="{{ $checkinUrl }}" target="_blank" rel="noopener"><i data-lucide="external-link"></i> Check-in</a>
                        <button type="button" class="orgmate-btn ghost orgmate-btn-sm" id="qrDownloadBtn"><i data-lucide="download"></i> Unduh QR</button>
                        <button type="button" class="orgmate-btn ghost orgmate-btn-sm" id="qrPrintBtn"><i data-lucide="printer"></i> Cetak</button>
                    </div>
                @endif
            </div>

            <div class="orgmate-card">
                <div class="orgmate-card-head">
                    <h2><i data-lucide="list-checks"></i> Daftar Kehadiran</h2>
                    <form method="GET" action="{{ route('simora.e-progress.presensi') }}" class="eprogress-attendance-search">
                        @if($selectedOrganization)<input type="hidden" name="organization" value="{{ $selectedOrganization->slug }}">@endif
                        @if($selectedActivity)<input type="hidden" name="activity" value="{{ $selectedActivity->id }}">@endif
                        <i data-lucide="search"></i>
                        <input type="search" name="q" value="{{ $search }}" placeholder="Cari nama peserta..." aria-label="Cari nama peserta">
                    </form>
                </div>
                <p class="orgmate-field-hint eprogress-attendance-count">
                    {{ ($search ? 'Menampilkan '.$attendances->count().' hasil untuk “'.$search.'”. ' : '') }}{{ $attendances->count() }} orang hadir.
                </p>
                <div class="orgmate-table-wrap">
                    <table class="orgmate-table">
                        <thead>
                            <tr><th>No</th><th>Nama</th><th>Metode</th><th>Waktu Check-in</th>@if(!($isDeveloper ?? false))<th>Aksi</th>@endif</tr>
                        </thead>
                        <tbody>
                            @forelse ($attendances as $index => $attendance)
                                <tr>
                                    <td class="orgmate-muted">{{ $index + 1 }}</td>
                                    <td>
                                        <span class="eprogress-attendee">
                                            <span class="eprogress-attendee-avatar">{{ strtoupper(\Illuminate\Support\Str::substr(trim($attendance->name), 0, 1)) }}</span>
                                            <strong>{{ $attendance->name }}</strong>
                                        </span>
                                    </td>
                                    <td>@if($attendance->method === 'qr')<span class="badge green">QR Code</span>@else<span class="badge gray">Manual</span>@endif</td>
                                    <td>{{ $attendance->checked_at?->locale('id')->translatedFormat('d M Y · H:i') ?? '—' }}</td>
                                    @if(!($isDeveloper ?? false))
                                        <td>
                                            <form method="POST" action="{{ route('simora.e-progress.presensi.destroy', $attendance) }}" onsubmit="return confirm('Hapus presensi {{ $attendance->name }}?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="orgmate-btn ghost orgmate-btn-sm">Hapus</button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr><td colspan="{{ ($isDeveloper ?? false) ? 4 : 5 }}"><div class="orgmate-empty">Belum ada peserta tercatat. Tayangkan QR di kiri atau catat manual di bawah.</div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if(!($isDeveloper ?? false))
            <section class="orgmate-card" aria-label="Tambah presensi manual">
                <div class="orgmate-card-head"><h2><i data-lucide="pen-line"></i> Catat Manual</h2></div>
                <form method="POST" action="{{ route('simora.e-progress.presensi.store') }}" class="orgmate-form">
                    @csrf
                    <input type="hidden" name="activity_id" value="{{ $selectedActivity?->id }}">
                    <div class="orgmate-form-grid">
                        <div class="orgmate-field"><label for="attendanceName">Nama peserta</label>
                            <input id="attendanceName" name="name" value="{{ old('name') }}" required>
                            @error('name')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    <button type="submit" class="orgmate-btn">Simpan Presensi</button>
                </form>
            </section>
        @endif
    @endif
    <script>
        (function () {
            const qrBox = document.getElementById('qrBox');
            const downloadBtn = document.getElementById('qrDownloadBtn');
            const printBtn = document.getElementById('qrPrintBtn');
            const printArea = document.getElementById('qrPrintArea');

            if (downloadBtn && qrBox) {
                downloadBtn.addEventListener('click', function () {
                    const svg = qrBox.querySelector('svg');
                    if (!svg) return;
                    const blob = new Blob([new XMLSerializer().serializeToString(svg)], { type: 'image/svg+xml;charset=utf-8' });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = 'qr-presensi.svg';
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    setTimeout(() => URL.revokeObjectURL(url), 1000);
                });
            }

            if (printBtn && printArea) {
                printBtn.addEventListener('click', function () {
                    printArea.classList.add('is-printing-qr');
                    window.print();
                    setTimeout(() => printArea.classList.remove('is-printing-qr'), 500);
                });
            }
        })();
    </script>
@endsection
