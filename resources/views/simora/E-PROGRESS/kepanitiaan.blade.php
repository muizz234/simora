@extends('simora.E-PROGRESS.layout', ['title' => 'Kepanitiaan | E-PROGRESS'])

@section('content')
    <div class="orgmate-intro">
        <div>
            <p class="orgmate-kicker">Kepanitiaan · Scope: {{ $orgName ?? 'Semua Organisasi' }}</p>
            <h1>Kepanitiaan Kegiatan</h1>
            <p class="lead">Susun susunan panitia untuk setiap kegiatan{{ ($isDeveloper ?? false) ? ' (mode monitoring developer)' : '' }}.</p>
        </div>
        <div class="orgmate-intro-actions">
            @if (($isDeveloper ?? false) && ($organizationsList ?? collect())->count() > 0)
                <form method="GET" action="{{ route('simora.e-progress.kepanitiaan') }}" class="orgmate-org-form orgmate-org-form--inline">
                    <label for="panitiaOrgSelect">Lihat organisasi:</label>
                    <select id="panitiaOrgSelect" name="organization" onchange="this.form.submit()">
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

    <div class="eprogress-stats eprogress-stats-3">
        <article class="eprogress-stat eprogress-stat--pastel-blue"><span class="eprogress-stat-icon"><i data-lucide="calendar-days" class="icon"></i></span><div><span>Kegiatan</span><strong>{{ $stats['totalKegiatan'] }}</strong><small>Terdaftar di kalender</small></div></article>
        <article class="eprogress-stat eprogress-stat--pastel-green"><span class="eprogress-stat-icon"><i data-lucide="calendar-check" class="icon"></i></span><div><span>Kegiatan Berpanitia</span><strong>{{ $stats['kegiatanBerpanitia'] }}</strong><small>Sudah punya susunan panitia</small></div></article>
        <article class="eprogress-stat eprogress-stat--pastel-purple"><span class="eprogress-stat-icon"><i data-lucide="users" class="icon"></i></span><div><span>Total Panitia</span><strong>{{ $stats['totalPanitia'] }}</strong><small>Seluruh kegiatan</small></div></article>
    </div>

    @if ($activities->isEmpty())
        <div class="orgmate-card">
            <div class="orgmate-empty">
                Belum ada kegiatan. Buat kegiatan dulu di
                <a href="{{ route('simora.e-progress.kalender') }}">Kalender Kegiatan</a>, lalu susun panitianya di sini.
            </div>
        </div>
    @else
        <div class="orgmate-card">
            <div class="orgmate-card-head">
                <h2><i data-lucide="flag" class="icon"></i> Pilih Kegiatan</h2>
                <form method="GET" action="{{ route('simora.e-progress.kepanitiaan') }}" class="orgmate-org-form orgmate-org-form--inline eprogress-panitia-select">
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
                <div class="orgmate-post" style="margin-bottom:12px">
                    <span class="orgmate-post-icon"><i data-lucide="calendar-check" class="icon"></i></span>
                    <div style="flex:1;min-width:0">
                        <h3>{{ $selectedActivity->name }}</h3>
                        <p>{{ $selectedActivity->description ?: ($selectedActivity->location ?: 'Tanpa keterangan tambahan.') }}</p>
                        <div class="orgmate-post-meta">
                            <span class="badge gray">{{ $selectedActivity->start_at?->locale('id')->translatedFormat('d M Y') ?? '—' }}</span>
                            @if($selectedActivity->workProgram)<span class="badge blue">{{ $selectedActivity->workProgram->name }}</span>@endif
                            <span class="badge gray">{{ $committees->count() }} panitia</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="orgmate-card">
            <div class="orgmate-card-head">
                <h2><i data-lucide="users" class="icon"></i> Susunan Panitia</h2>
                <span class="badge gray">{{ $committees->count() }} orang</span>
            </div>
            <div class="orgmate-table-wrap">
                <table class="orgmate-table">
                    <thead>
                        <tr><th>Nama Panitia</th><th>Peran / Jabatan</th>@if(!($isDeveloper ?? false))<th>Aksi</th>@endif</tr>
                    </thead>
                    <tbody>
                        @forelse ($committees as $committee)
                            <tr>
                                <td><strong>{{ $committee->name }}</strong></td>
                                <td><span class="badge blue">{{ $committee->role }}</span></td>
                                @if(!($isDeveloper ?? false))
                                    <td>
                                        <span class="orgmate-position-actions">
                                            <button type="button" class="orgmate-btn ghost orgmate-btn-sm" data-edit-open data-dialog="editCommittee-{{ $committee->id }}">Edit</button>
                                            <form method="POST" action="{{ route('simora.e-progress.kepanitiaan.destroy', $committee) }}" onsubmit="return confirm('Hapus panitia {{ $committee->name }}?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="orgmate-btn ghost orgmate-btn-sm">Hapus</button>
                                            </form>
                                        </span>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr><td colspan="{{ ($isDeveloper ?? false) ? 2 : 3 }}"><div class="orgmate-empty">Belum ada panitia untuk kegiatan ini. Tambahkan melalui form di bawah.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(!($isDeveloper ?? false))
            <section class="orgmate-card" aria-label="Tambah panitia">
                <div class="orgmate-card-head"><h2><i data-lucide="user-plus" class="icon"></i> Tambah Panitia</h2></div>
                <form method="POST" action="{{ route('simora.e-progress.kepanitiaan.store') }}" class="orgmate-form">
                    @csrf
                    <div class="orgmate-form-grid">
                        <div class="orgmate-field"><label for="committeeActivity">Kegiatan</label>
                            <select id="committeeActivity" name="activity_id">
                                @foreach ($activities as $activity)
                                    <option value="{{ $activity->id }}" @selected(old('activity_id', $selectedActivity?->id) == $activity->id)>{{ $activity->name }}</option>
                                @endforeach
                            </select>
                            @error('activity_id')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label for="committeeName">Nama panitia</label>
                            <input id="committeeName" name="name" value="{{ old('name') }}" list="memberNames" required>
                            <datalist id="memberNames">
                                @foreach ($members as $member)<option value="{{ $member->name }}">@endforeach
                            </datalist>
                            @error('name')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label for="committeeRole">Peran / Jabatan</label>
                            <input id="committeeRole" name="role" value="{{ old('role') }}" list="roleSuggestions" required>
                            <datalist id="roleSuggestions">
                                @foreach (['Ketua Pelaksana','Wakil Ketua Pelaksana','Sekretaris','Bendahara','Koordinator Acara','Koordinator Humas','Koordinator Kominfo','Koordinator Logistik','Anggota'] as $suggestion)<option value="{{ $suggestion }}">@endforeach
                            </datalist>
                            @error('role')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    <button type="submit" class="orgmate-btn">Simpan Panitia</button>
                </form>
            </section>

            @foreach ($committees as $committee)
                <dialog class="orgmate-dialog" id="editCommittee-{{ $committee->id }}" aria-labelledby="editCommitteeTitle-{{ $committee->id }}">
                    <form method="dialog" class="orgmate-dialog-close-form" value="close">
                        <button type="submit" class="orgmate-icon-btn orgmate-dialog-close" aria-label="Tutup"><i data-lucide="x" class="icon" aria-hidden="true"></i></button>
                    </form>
                    <h2 class="orgmate-dialog-title" id="editCommitteeTitle-{{ $committee->id }}">Edit Panitia</h2>
                    <form method="POST" action="{{ route('simora.e-progress.kepanitiaan.update', $committee) }}">
                        @csrf @method('PUT')
                        <div class="orgmate-form-grid">
                            <div class="orgmate-field"><label>Nama panitia</label>
                                <input name="name" value="{{ old('name', $committee->name) }}" list="memberNames" required>
                                @error('name')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                            </div>
                            <div class="orgmate-field"><label>Peran / Jabatan</label>
                                <input name="role" value="{{ old('role', $committee->role) }}" list="roleSuggestions" required>
                                @error('role')<small class="orgmate-field-error">{{ $message }}</small>@enderror
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
