@extends('simora.E-PROGRESS.layout', ['title' => 'Evaluasi & LPJ | E-PROGRESS'])

@section('content')
    <div class="orgmate-intro">
        <div>
            <p class="orgmate-kicker">Evaluasi &amp; LPJ Kegiatan · Scope: {{ $orgName ?? 'Semua Organisasi' }}</p>
            <h1>Evaluasi &amp; LPJ Kegiatan</h1>
            <p class="lead">Catat hasil evaluasi dan laporan pertanggungjawaban setiap kegiatan{{ ($isDeveloper ?? false) ? ' (mode monitoring developer)' : '' }}.</p>
        </div>
        <div class="orgmate-intro-actions">
            @if (($isDeveloper ?? false) && ($organizationsList ?? collect())->count() > 0)
                <form method="GET" action="{{ route('simora.e-progress.evaluasi') }}" class="orgmate-org-form orgmate-org-form--inline">
                    <label for="evalOrgSelect">Lihat organisasi:</label>
                    <select id="evalOrgSelect" name="organization" onchange="this.form.submit()">
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
                <a href="{{ route('simora.e-progress.kalender') }}">Kalender Kegiatan</a>, lalu tulis evaluasinya di sini.
            </div>
        </div>
    @else
        <div class="eprogress-stats eprogress-stats-3">
            <article class="eprogress-stat eprogress-stat--pastel-blue"><span class="eprogress-stat-icon"><i data-lucide="clipboard-list" class="icon"></i></span><div><span>Total Dokumen</span><strong>{{ $stats['total'] }}</strong><small>Pada kegiatan terpilih</small></div></article>
            <article class="eprogress-stat eprogress-stat--pastel-green"><span class="eprogress-stat-icon"><i data-lucide="search-check" class="icon"></i></span><div><span>Evaluasi</span><strong>{{ $stats['evaluasi'] }}</strong><small>Catatan evaluasi</small></div></article>
            <article class="eprogress-stat eprogress-stat--pastel-amber"><span class="eprogress-stat-icon"><i data-lucide="file-text" class="icon"></i></span><div><span>LPJ</span><strong>{{ $stats['lpj'] }}</strong><small>Laporan pertanggungjawaban</small></div></article>
        </div>

        <div class="orgmate-card">
            <div class="orgmate-card-head">
                <h2><i data-lucide="flag" class="icon"></i> Pilih Kegiatan</h2>
                <form method="GET" action="{{ route('simora.e-progress.evaluasi') }}" class="orgmate-org-form orgmate-org-form--inline eprogress-panitia-select">
                    @if($selectedOrganization)<input type="hidden" name="organization" value="{{ $selectedOrganization->slug }}">@endif
                    <label for="activitySelect">Kegiatan:</label>
                    <select id="activitySelect" name="activity" onchange="this.form.submit()">
                        @foreach ($activities as $activity)
                            <option value="{{ $activity->id }}" {{ $selectedActivity?->id === $activity->id ? 'selected' : '' }}>{{ $activity->name }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            @if ($selectedActivity)
                <div class="orgmate-post" style="margin-bottom:0">
                    <span class="orgmate-post-icon"><i data-lucide="calendar-check" class="icon"></i></span>
                    <div style="flex:1;min-width:0">
                        <h3>{{ $selectedActivity->name }}</h3>
                        <p>{{ $selectedActivity->description ?: ($selectedActivity->location ?: 'Tanpa keterangan tambahan.') }}</p>
                        <div class="orgmate-post-meta">
                            <span class="badge gray">{{ $selectedActivity->start_at?->locale('id')->translatedFormat('d M Y') ?? '—' }}</span>
                            @if($selectedActivity->workProgram)<span class="badge blue">{{ $selectedActivity->workProgram->name }}</span>@endif
                            <span class="badge gray">{{ $evaluations->count() }} dokumen</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="eprogress-tabs" style="margin-bottom:14px">
            <a class="badge {{ $typeFilter === null ? 'blue' : 'gray' }}" href="{{ route('simora.e-progress.evaluasi', array_merge($selectedOrganization ? ['organization' => $selectedOrganization->slug] : [], $selectedActivity ? ['activity' => $selectedActivity->id] : [])) }}">Semua</a>
            <a class="badge {{ $typeFilter === 'evaluasi' ? 'blue' : 'gray' }}" href="{{ route('simora.e-progress.evaluasi', array_merge($selectedOrganization ? ['organization' => $selectedOrganization->slug] : [], $selectedActivity ? ['activity' => $selectedActivity->id] : [], ['type' => 'evaluasi'])) }}">Evaluasi</a>
            <a class="badge {{ $typeFilter === 'lpj' ? 'blue' : 'gray' }}" href="{{ route('simora.e-progress.evaluasi', array_merge($selectedOrganization ? ['organization' => $selectedOrganization->slug] : [], $selectedActivity ? ['activity' => $selectedActivity->id] : [], ['type' => 'lpj'])) }}">LPJ</a>
        </div>

        <div class="orgmate-card">
            <div class="orgmate-card-head">
                <h2><i data-lucide="scroll-text" class="icon"></i> Daftar Dokumen</h2>
                <span class="badge gray">{{ $evaluations->count() }} dokumen</span>
            </div>
            @forelse ($evaluations as $evaluation)
                <div class="orgmate-post">
                    <span class="orgmate-post-icon"><i data-lucide="{{ $evaluation->type === 'lpj' ? 'file-text' : 'search-check' }}" class="icon"></i></span>
                    <div style="flex:1;min-width:0">
                        <h3>{{ $evaluation->title }}</h3>
                        <p>{{ \Illuminate\Support\Str::limit($evaluation->content ?: 'Tanpa isi.', 160) }}</p>
                        <div class="orgmate-post-meta">
                            @if($evaluation->type === 'lpj')
                                <span class="badge green">LPJ</span>
                            @else
                                <span class="badge blue">Evaluasi</span>
                            @endif
                            @if($evaluation->status === 'selesai')
                                <span class="badge green">Selesai</span>
                            @else
                                <span class="badge amber">Draft</span>
                            @endif
                            @if($evaluation->creator)<span class="badge gray">oleh {{ $evaluation->creator->name }}</span>@endif
                            <span class="badge gray">{{ $evaluation->created_at?->locale('id')->translatedFormat('d M Y') ?? '—' }}</span>
                        </div>
                        @if(!($isDeveloper ?? false))
                            <div class="orgmate-position-actions" style="margin-top:8px">
                                <button type="button" class="orgmate-btn ghost orgmate-btn-sm" data-edit-open data-dialog="editEval-{{ $evaluation->id }}">Edit</button>
                                <form method="POST" action="{{ route('simora.e-progress.evaluasi.destroy', $evaluation) }}" onsubmit="return confirm('Hapus dokumen {{ $evaluation->title }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="orgmate-btn ghost orgmate-btn-sm">Hapus</button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="orgmate-empty">Belum ada dokumen. Tulis evaluasi atau LPJ pertama melalui form di bawah.</div>
            @endforelse
        </div>

        @if(!($isDeveloper ?? false))
            <section class="orgmate-card" aria-label="Tambah evaluasi atau LPJ">
                <div class="orgmate-card-head"><h2><i data-lucide="pen-line" class="icon"></i> Tulis Dokumen</h2></div>
                <form method="POST" action="{{ route('simora.e-progress.evaluasi.store') }}" class="orgmate-form">
                    @csrf
                    <div class="orgmate-form-grid">
                        <div class="orgmate-field"><label for="evalActivity">Kegiatan</label>
                            <select id="evalActivity" name="activity_id">
                                @foreach ($activities as $activity)
                                    <option value="{{ $activity->id }}" @selected(old('activity_id', $selectedActivity?->id) == $activity->id)>{{ $activity->name }}</option>
                                @endforeach
                            </select>
                            @error('activity_id')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label for="evalTitle">Judul</label>
                            <input id="evalTitle" name="title" value="{{ old('title') }}" required>
                            @error('title')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label for="evalType">Tipe</label>
                            <select id="evalType" name="type">
                                <option value="evaluasi" @selected(old('type') === 'evaluasi')>Evaluasi</option>
                                <option value="lpj" @selected(old('type') === 'lpj')>LPJ</option>
                            </select>
                            @error('type')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label for="evalStatus">Status</label>
                            <select id="evalStatus" name="status">
                                <option value="draft" @selected(old('status', 'draft') === 'draft')>Draft</option>
                                <option value="selesai" @selected(old('status') === 'selesai')>Selesai</option>
                            </select>
                            @error('status')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field orgmate-field--wide"><label for="evalContent">Isi dokumen</label>
                            <textarea id="evalContent" name="content" rows="6">{{ old('content') }}</textarea>
                            @error('content')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    <button type="submit" class="orgmate-btn">Simpan Dokumen</button>
                </form>
            </section>

            @foreach ($evaluations as $evaluation)
                <dialog class="orgmate-dialog" id="editEval-{{ $evaluation->id }}" aria-labelledby="editEvalTitle-{{ $evaluation->id }}">
                    <form method="dialog" class="orgmate-dialog-close-form" value="close">
                        <button type="submit" class="orgmate-icon-btn orgmate-dialog-close" aria-label="Tutup"><i data-lucide="x" class="icon" aria-hidden="true"></i></button>
                    </form>
                    <h2 class="orgmate-dialog-title" id="editEvalTitle-{{ $evaluation->id }}">Edit Dokumen</h2>
                    <form method="POST" action="{{ route('simora.e-progress.evaluasi.update', $evaluation) }}">
                        @csrf @method('PUT')
                        <div class="orgmate-form-grid">
                            <div class="orgmate-field"><label>Judul</label>
                                <input name="title" value="{{ old('title', $evaluation->title) }}" required>
                                @error('title')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                            </div>
                            <div class="orgmate-field"><label>Tipe</label>
                                <select name="type">
                                    <option value="evaluasi" @selected(old('type', $evaluation->type) === 'evaluasi')>Evaluasi</option>
                                    <option value="lpj" @selected(old('type', $evaluation->type) === 'lpj')>LPJ</option>
                                </select>
                                @error('type')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                            </div>
                            <div class="orgmate-field"><label>Status</label>
                                <select name="status">
                                    <option value="draft" @selected(old('status', $evaluation->status) === 'draft')>Draft</option>
                                    <option value="selesai" @selected(old('status', $evaluation->status) === 'selesai')>Selesai</option>
                                </select>
                                @error('status')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                            </div>
                            <div class="orgmate-field orgmate-field--wide"><label>Isi dokumen</label>
                                <textarea name="content" rows="6">{{ old('content', $evaluation->content) }}</textarea>
                                @error('content')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="orgmate-dialog-actions">
                            <button type="button" class="orgmate-btn ghost" data-edit-close>Batal</button>
                            <button type="submit" class="orgmate-btn">Simpan Perubahan</button>
                        </div>
                    </form>
                </dialog>
            @endforeach
            <script>
                document.querySelectorAll('[data-edit-open]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const dialog = document.getElementById(button.dataset.dialog);
                        if (dialog && typeof dialog.showModal === 'function') dialog.showModal();
                    });
                });
                document.querySelectorAll('[data-edit-close]').forEach((button) => {
                    button.addEventListener('click', () => { button.closest('dialog')?.close(); });
                });
                document.querySelectorAll('.orgmate-dialog').forEach((dialog) => {
                    dialog.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });
                });
            </script>
        @endif
    @endif
@endsection
