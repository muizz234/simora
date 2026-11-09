@extends('simora.E-PROGRESS.layout', ['title' => 'Program Kerja | E-PROGRESS'])

@section('content')
    <div class="orgmate-intro">
        <div>
            <p class="orgmate-kicker">Program Kerja · Scope: {{ $orgName ?? 'Semua Organisasi' }}</p>
            <h1>Program Kerja</h1>
            <p class="lead">Daftar program kerja organisasi{{ ($isDeveloper ?? false) ? ' (mode monitoring developer)' : '' }}.</p>
        </div>
        <div class="orgmate-intro-actions">
            @if (($isDeveloper ?? false) && ($organizationsList ?? collect())->count() > 0)
                <form method="GET" action="{{ route('simora.e-progress.proker') }}" class="orgmate-org-form orgmate-org-form--inline">
                    <label for="prokerOrgSelect">Lihat organisasi:</label>
                    <select id="prokerOrgSelect" name="organization" onchange="this.form.submit()">
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

    <div class="orgmate-card">
        <div class="orgmate-table-wrap">
            <table class="orgmate-table">
                <thead>
                    <tr>
                        <th>Program Kerja</th>
                        <th>Status</th>
                        <th>Mulai</th>
                        <th>Selesai</th>
                        @if (!($isDeveloper ?? false))
                            <th>Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($workPrograms as $program)
                        <tr>
                            <td><strong>{{ $program->name }}</strong><small class="orgmate-field-hint">{{ $program->description ?: '—' }}</small></td>
                            <td><span class="badge blue">{{ ucfirst($program->status) }}</span></td>
                            <td>{{ $program->start_date ? $program->start_date->locale('id')->translatedFormat('d M Y') : '—' }}</td>
                            <td>{{ $program->end_date ? $program->end_date->locale('id')->translatedFormat('d M Y') : '—' }}</td>
                            @if (!($isDeveloper ?? false))
                                <td>
                                    <span class="orgmate-position-actions">
                                        <button type="button" class="orgmate-btn ghost orgmate-btn-sm" data-edit-open data-dialog="editProgram-{{ $program->id }}">Edit</button>
                                        <form method="POST" action="{{ route('simora.e-progress.proker.destroy', $program) }}" onsubmit="return confirm('Hapus program kerja {{ $program->name }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="orgmate-btn ghost orgmate-btn-sm">Hapus</button>
                                        </form>
                                    </span>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ ($isDeveloper ?? false) ? 4 : 5 }}">
                                <div class="orgmate-empty">Belum ada program kerja. Tambahkan proker pertama melalui form di bawah.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="admin-pagination">{{ $workPrograms->links() }}</div>
    </div>

    @if (!($isDeveloper ?? false))
        <section class="orgmate-card" aria-label="Tambah program kerja">
            <div class="orgmate-card-head">
                <h2><i data-lucide="plus" class="icon"></i> Tambah Program Kerja</h2>
            </div>
            <form method="POST" action="{{ route('simora.e-progress.proker.store') }}" class="orgmate-form">
                @csrf
                <div class="orgmate-form-grid">
                    <div class="orgmate-field orgmate-field--wide"><label for="programName">Nama program kerja</label>
                        <input id="programName" name="name" value="{{ old('name') }}" required>
                        @error('name')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field orgmate-field--wide"><label for="programDesc">Deskripsi <span>(opsional)</span></label>
                        <textarea id="programDesc" name="description" rows="2">{{ old('description') }}</textarea>
                        @error('description')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field"><label for="programStatus">Status</label>
                        <select id="programStatus" name="status">
                            @foreach (['perencanaan', 'pelaksanaan', 'evaluasi', 'pelaporan', 'selesai'] as $stage)
                                <option value="{{ $stage }}" @selected(old('status') === $stage)>{{ ucfirst($stage) }}</option>
                            @endforeach
                        </select>
                        @error('status')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field"><label for="programStart">Tanggal mulai <span>(opsional)</span></label>
                        <input id="programStart" name="start_date" type="date" value="{{ old('start_date') }}">
                        @error('start_date')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field"><label for="programEnd">Tanggal selesai <span>(opsional)</span></label>
                        <input id="programEnd" name="end_date" type="date" value="{{ old('end_date') }}">
                        @error('end_date')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                </div>
                <button type="submit" class="orgmate-btn">Simpan Program Kerja</button>
            </form>
        </section>

        @foreach ($workPrograms as $program)
            <dialog class="orgmate-dialog" id="editProgram-{{ $program->id }}" aria-labelledby="editProgramTitle-{{ $program->id }}">
                <form method="dialog" class="orgmate-dialog-close-form" value="close">
                    <button type="submit" class="orgmate-icon-btn orgmate-dialog-close" aria-label="Tutup"><i data-lucide="x" class="icon" aria-hidden="true"></i></button>
                </form>
                <h2 class="orgmate-dialog-title" id="editProgramTitle-{{ $program->id }}">Edit Program Kerja</h2>
                <form method="POST" action="{{ route('simora.e-progress.proker.update', $program) }}">
                    @csrf @method('PUT')
                    <div class="orgmate-form-grid">
                        <div class="orgmate-field orgmate-field--wide"><label>Nama program kerja</label>
                            <input name="name" value="{{ old('name', $program->name) }}" required>
                            @error('name')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field orgmate-field--wide"><label>Deskripsi <span>(opsional)</span></label>
                            <textarea name="description" rows="2">{{ old('description', $program->description) }}</textarea>
                            @error('description')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label>Status</label>
                            <select name="status">
                                @foreach (['perencanaan', 'pelaksanaan', 'evaluasi', 'pelaporan', 'selesai'] as $stage)
                                    <option value="{{ $stage }}" @selected(old('status', $program->status) === $stage)>{{ ucfirst($stage) }}</option>
                                @endforeach
                            </select>
                            @error('status')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label>Tanggal mulai <span>(opsional)</span></label>
                            <input name="start_date" type="date" value="{{ old('start_date', $program->start_date) }}">
                            @error('start_date')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label>Tanggal selesai <span>(opsional)</span></label>
                            <input name="end_date" type="date" value="{{ old('end_date', $program->end_date) }}">
                            @error('end_date')<small class="orgmate-field-error">{{ $message }}</small>@enderror
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
