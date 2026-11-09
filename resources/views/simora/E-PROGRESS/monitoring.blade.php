@extends('simora.E-PROGRESS.layout', ['title' => 'Monitoring Progres Proker | E-PROGRESS'])

@section('content')
    <div class="orgmate-intro">
        <div>
            <p class="orgmate-kicker">Monitoring Progres Proker · Scope: {{ $orgName ?? 'Semua Organisasi' }}</p>
            <h1>Monitoring Progres Proker</h1>
            <p class="lead">Kemajuan program kerja berdasarkan kegiatan yang sudah selesai.</p>
        </div>
        <div class="orgmate-intro-actions">
            @if (($isDeveloper ?? false) && ($organizationsList ?? collect())->count() > 0)
                <form method="GET" action="{{ route('simora.e-progress.monitoring') }}" class="orgmate-org-form orgmate-org-form--inline">
                    <label for="monitorOrgSelect">Lihat organisasi:</label>
                    <select id="monitorOrgSelect" name="organization" onchange="this.form.submit()">
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

    <div class="eprogress-stats eprogress-stats-3">
        <article class="eprogress-stat eprogress-stat--pastel-blue"><span class="eprogress-stat-icon"><i data-lucide="layers" class="icon"></i></span><div><span>Total Proker</span><strong>{{ $stats['totalProker'] }}</strong><small>Program kerja terdaftar</small></div></article>
        <article class="eprogress-stat eprogress-stat--pastel-green"><span class="eprogress-stat-icon"><i data-lucide="gauge" class="icon"></i></span><div><span>Rata-rata Progres</span><strong>{{ $stats['rataRata'] }}%</strong><small>Kegiatan selesai / total kegiatan</small></div></article>
        <article class="eprogress-stat eprogress-stat--pastel-amber"><span class="eprogress-stat-icon"><i data-lucide="flag" class="icon"></i></span><div><span>Proker 100% &amp; Belum Ada Kegiatan</span><strong>{{ $stats['selesai'] }} / {{ $stats['belumMulai'] }}</strong><small>Selesai penuh / belum berjalan</small></div></article>
    </div>

    @if ($summaries->isEmpty())
        <div class="orgmate-card">
            <div class="orgmate-empty">Belum ada program kerja. Tambahkan proker dulu di <a href="{{ route('simora.e-progress.proker') }}">menu Proker</a>.</div>
        </div>
    @else
        <div class="orgmate-card">
            <div class="orgmate-card-head">
                <h2><i data-lucide="chart-column" class="icon"></i> Progres per Program Kerja</h2>
                <span class="badge gray">{{ $summaries->count() }} proker</span>
            </div>
            <div class="orgmate-table-wrap">
                <table class="orgmate-table">
                    <thead>
                        <tr>
                            <th>Program Kerja</th>
                            <th>Kegiatan</th>
                            <th>Progres</th>
                            <th>Rincian Tahapan</th>
                            <th>Kegiatan Terakhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($summaries as $summary)
                            @php $program = $summary['program']; @endphp
                            <tr>
                                <td>
                                    <strong>{{ $program->name }}</strong>
                                    <small class="orgmate-field-hint">{{ $program->description ?: '—' }}</small>
                                    <small class="orgmate-field-hint">
                                        @if(($isDeveloper ?? false)){{ $program->organization?->name }} · @endif{{ $program->start_date?->locale('id')->translatedFormat('d M Y') ?? '—' }} s.d. {{ $program->end_date?->locale('id')->translatedFormat('d M Y') ?? '—' }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge blue">{{ $summary['selesai'] }}/{{ $summary['totalKegiatan'] }} selesai</span>
                                </td>
                                <td>
                                    <div class="eprogress-progress">
                                        <div class="eprogress-progress-bar">
                                            <span style="width: {{ $summary['progress'] }}%"></span>
                                        </div>
                                        <strong>{{ $summary['progress'] }}%</strong>
                                    </div>
                                    @if ($summary['totalKegiatan'] === 0)
                                        <small class="orgmate-field-hint">Belum ada kegiatan</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="eprogress-stage-chips">
                                        @foreach (['perencanaan', 'pelaksanaan', 'evaluasi', 'pelaporan', 'selesai'] as $stage)
                                            @if (($summary['byStage'][$stage] ?? 0) > 0)
                                                <span class="badge gray">{{ ucfirst($stage) }}: {{ $summary['byStage'][$stage] }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    @if($summary['lastActivity'])
                                        <strong>{{ $summary['lastActivity']->name }}</strong>
                                        <small class="orgmate-field-hint">{{ $summary['lastActivity']->start_at?->locale('id')->translatedFormat('d M Y') ?? '—' }}</small>
                                    @else
                                        <span class="orgmate-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
