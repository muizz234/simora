@extends('simora.E-PROGRESS.layout', ['title' => 'Kalender Kegiatan | E-PROGRESS'])

@section('content')
    <div class="orgmate-intro">
        <div>
            <p class="orgmate-kicker">Kalender Kegiatan · Scope: {{ $orgName ?? 'Semua Organisasi' }}</p>
            <h1>Kalender Kegiatan</h1>
            <p class="lead">Jadwal kegiatan organisasi bulan ini{{ ($isDeveloper ?? false) ? ' (mode monitoring developer)' : '' }}.</p>
        </div>
        <div class="orgmate-intro-actions">
            @if (($isDeveloper ?? false) && ($organizationsList ?? collect())->count() > 0)
                <form method="GET" action="{{ route('simora.e-progress.kalender') }}" class="orgmate-org-form orgmate-org-form--inline">
                    <label for="kalenderOrgSelect">Lihat organisasi:</label>
                    <select id="kalenderOrgSelect" name="organization" onchange="this.form.submit()">
                        <option value="">Semua Organisasi</option>
                        @foreach ($organizationsList as $org)
                            <option value="{{ $org->slug }}" {{ $selectedOrganization?->slug === $org->slug ? 'selected' : '' }}>{{ $org->name }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="year" value="{{ $year }}">
                    <input type="hidden" name="month" value="{{ $month }}">
                </form>
            @endif
            <a class="orgmate-back" href="{{ route('simora.e-progress') }}">← Kembali</a>
        </div>
    </div>

    @if (session('status'))
        <div class="orgmate-toast is-show" style="position:static;opacity:1;transform:none;margin-bottom:12px">{{ session('status') }}</div>
    @endif

    <div class="orgmate-card">
        <div class="orgmate-card-head">
            <h2><i data-lucide="calendar-days" class="icon"></i> {{ $prev->copy()->locale('id')->translatedFormat('F Y') }}</h2>
            <div class="eprogress-cal-nav">
                <a class="orgmate-btn ghost orgmate-btn-sm" href="{{ route('simora.e-progress.kalender', array_merge(request()->only('organization'), ['year' => $prev->format('Y'), 'month' => $prev->format('m')])) }}">‹ Sebelumnya</a>
                <a class="orgmate-btn ghost orgmate-btn-sm" href="{{ route('simora.e-progress.kalender', array_merge(request()->only('organization'), ['year' => $next->format('Y'), 'month' => $next->format('m')])) }}">Berikutnya ›</a>
            </div>
        </div>
        <div class="eprogress-cal-head">
            @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $dayName)
                <span>{{ $dayName }}</span>
            @endforeach
        </div>
        <div class="eprogress-cal-grid">
            @foreach ($days as $day)
                @php $dayKey = $day['date']->format('Y-m-d'); @endphp
                <div class="eprogress-cal-day {{ $day['inMonth'] ? '' : 'is-outside' }} {{ $dayKey === now()->format('Y-m-d') ? 'is-today' : '' }}">
                    <span class="eprogress-cal-date">{{ $day['date']->format('j') }}</span>
                    @foreach ($activitiesByDay[$dayKey] ?? [] as $activity)
                        <span class="eprogress-cal-chip" title="{{ $activity->name }}">{{ $activity->name }}</span>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>

    <div class="orgmate-card">
        <div class="orgmate-card-head">
            <h2><i data-lucide="list" class="icon"></i> Kegiatan Bulan Ini</h2>
            <span class="badge gray">{{ $monthActivities->count() }} kegiatan</span>
        </div>
        @forelse ($monthActivities as $activity)
            <div class="orgmate-post">
                <span class="orgmate-post-icon"><i data-lucide="calendar-check" class="icon"></i></span>
                <div style="flex:1;min-width:0">
                    <h3>{{ $activity->name }}</h3>
                    <p>{{ $activity->description ?: ($activity->location ?: 'Tanpa keterangan tambahan.') }}</p>
                    <div class="orgmate-post-meta">
                        <span class="orgmate-period">{{ ucfirst($activity->status) }}</span>
                        @if ($activity->workProgram)<span class="badge blue">{{ $activity->workProgram->name }}</span>@endif
                        <span class="badge gray">{{ $activity->start_at?->locale('id')->translatedFormat('d M Y · H:i') ?? '—' }}</span>
                    </div>
                    @if (!($isDeveloper ?? false))
                        <div class="orgmate-position-actions" style="margin-top:8px">
                            <button type="button" class="orgmate-btn ghost orgmate-btn-sm" data-edit-open data-dialog="editActivity-{{ $activity->id }}">Edit</button>
                            <form method="POST" action="{{ route('simora.e-progress.kalender.destroy', $activity) }}" onsubmit="return confirm('Hapus kegiatan {{ $activity->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="orgmate-btn ghost orgmate-btn-sm">Hapus</button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="orgmate-empty">Belum ada kegiatan pada bulan ini.</div>
        @endforelse
    </div>

    @if (!($isDeveloper ?? false))
        <section class="orgmate-card" aria-label="Tambah kegiatan">
            <div class="orgmate-card-head">
                <h2><i data-lucide="plus" class="icon"></i> Tambah Kegiatan</h2>
            </div>
            <form method="POST" action="{{ route('simora.e-progress.kalender.store') }}" class="orgmate-form">
                @csrf
                <div class="orgmate-form-grid">
                    <div class="orgmate-field orgmate-field--wide"><label for="activityName">Nama kegiatan</label>
                        <input id="activityName" name="name" value="{{ old('name') }}" required>
                        @error('name')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field"><label for="activityProgram">Program kerja</label>
                        <select id="activityProgram" name="work_program_id">
                            <option value="">— Tanpa proker —</option>
                            @foreach ($workPrograms as $program)
                                <option value="{{ $program->id }}" @selected(old('work_program_id') == $program->id)>{{ $program->name }}</option>
                            @endforeach
                        </select>
                        @error('work_program_id')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field"><label for="activityLocation">Lokasi <span>(opsional)</span></label>
                        <input id="activityLocation" name="location" value="{{ old('location') }}">
                        @error('location')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field"><label for="activityStatus">Status</label>
                        <select id="activityStatus" name="status">
                            @foreach (['perencanaan', 'pelaksanaan', 'evaluasi', 'pelaporan', 'selesai'] as $stage)
                                <option value="{{ $stage }}" @selected(old('status', 'perencanaan') === $stage)>{{ ucfirst($stage) }}</option>
                            @endforeach
                        </select>
                        @error('status')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field"><label for="activityStart">Dimulai</label>
                        <input id="activityStart" name="start_at" type="datetime-local" value="{{ old('start_at') }}" required>
                        @error('start_at')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field"><label for="activityEnd">Selesai <span>(opsional)</span></label>
                        <input id="activityEnd" name="end_at" type="datetime-local" value="{{ old('end_at') }}">
                        @error('end_at')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field orgmate-field--wide"><label for="activityDesc">Deskripsi <span>(opsional)</span></label>
                        <textarea id="activityDesc" name="description" rows="2">{{ old('description') }}</textarea>
                        @error('description')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                </div>
                <button type="submit" class="orgmate-btn">Simpan Kegiatan</button>
            </form>
        </section>

        @foreach ($monthActivities as $activity)
            <dialog class="orgmate-dialog" id="editActivity-{{ $activity->id }}" aria-labelledby="editActivityTitle-{{ $activity->id }}">
                <form method="dialog" class="orgmate-dialog-close-form" value="close">
                    <button type="submit" class="orgmate-icon-btn orgmate-dialog-close" aria-label="Tutup"><i data-lucide="x" class="icon" aria-hidden="true"></i></button>
                </form>
                <h2 class="orgmate-dialog-title" id="editActivityTitle-{{ $activity->id }}">Edit Kegiatan</h2>
                <form method="POST" action="{{ route('simora.e-progress.kalender.update', $activity) }}">
                    @csrf @method('PUT')
                    <div class="orgmate-form-grid">
                        <div class="orgmate-field orgmate-field--wide"><label>Nama kegiatan</label>
                            <input name="name" value="{{ old('name', $activity->name) }}" required>
                            @error('name')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label>Program kerja</label>
                            <select name="work_program_id">
                                <option value="">— Tanpa proker —</option>
                                @foreach ($workPrograms as $program)
                                    <option value="{{ $program->id }}" @selected(old('work_program_id', $activity->work_program_id) == $program->id)>{{ $program->name }}</option>
                                @endforeach
                            </select>
                            @error('work_program_id')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label>Lokasi <span>(opsional)</span></label>
                            <input name="location" value="{{ old('location', $activity->location) }}">
                            @error('location')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label>Status</label>
                            <select name="status">
                                @foreach (['perencanaan', 'pelaksanaan', 'evaluasi', 'pelaporan', 'selesai'] as $stage)
                                    <option value="{{ $stage }}" @selected(old('status', $activity->status) === $stage)>{{ ucfirst($stage) }}</option>
                                @endforeach
                            </select>
                            @error('status')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label>Dimulai</label>
                            <input name="start_at" type="datetime-local" value="{{ old('start_at', $activity->start_at?->format('Y-m-d\TH:i')) }}" required>
                            @error('start_at')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label>Selesai <span>(opsional)</span></label>
                            <input name="end_at" type="datetime-local" value="{{ old('end_at', $activity->end_at?->format('Y-m-d\TH:i')) }}">
                            @error('end_at')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field orgmate-field--wide"><label>Deskripsi <span>(opsional)</span></label>
                            <textarea name="description" rows="2">{{ old('description', $activity->description) }}</textarea>
                            @error('description')<small class="orgmate-field-error">{{ $message }}</small>@enderror
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
                button.addEventListener('click', () => {
                    button.closest('dialog')?.close();
                });
            });
            document.querySelectorAll('.orgmate-dialog').forEach((dialog) => {
                dialog.addEventListener('click', (event) => {
                    if (event.target === dialog) dialog.close();
                });
            });
        </script>
    @endif
@endsection
