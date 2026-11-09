@extends('simora.ORGMATE.layout', ['title' => 'Arsip SK | ORGMATE'])
@section('breadcrumb', 'Arsip SK & Surat')

@section('content')
    <div class="orgmate-intro">
        <div>
            <p class="orgmate-kicker">Arsip dan Dokumen · Scope: {{ $orgName ?? 'Semua Organisasi' }}</p>
            <h1>Arsip SK &amp; Surat Menyurat</h1>
            <p class="lead">Simpan dan kelola surat keputusan dan surat menyurat organisasi{{ ($isDeveloper ?? false) ? ' (mode monitoring developer)' : '' }}.</p>
        </div>
        <div class="orgmate-intro-actions">
            @if (($isDeveloper ?? false) && ($organizationsList ?? collect())->count() > 0)
                <form method="GET" action="{{ route('simora.orgmate.arsip') }}" class="orgmate-org-form orgmate-org-form--inline">
                    <label for="arsipOrgSelect">Lihat organisasi:</label>
                    <select id="arsipOrgSelect" name="organization" onchange="this.form.submit()">
                        <option value="">Semua Organisasi</option>
                        @foreach ($organizationsList as $org)
                            <option value="{{ $org->slug }}" {{ $selectedOrganization?->slug === $org->slug ? 'selected' : '' }}>{{ $org->name }}</option>
                        @endforeach
                    </select>
                </form>
            @endif
            <a class="orgmate-back" href="{{ route('simora.orgmate') }}">← Kembali</a>
        </div>
    </div>

    @if (session('status'))
        <div class="orgmate-toast is-show" style="position:static;opacity:1;transform:none;margin-bottom:12px">{{ session('status') }}</div>
    @endif

    <div class="orgmate-card">
        <div class="orgmate-kv orgmate-kv-3">
            <div><span>SK Organisasi</span><strong>{{ $counts['sk'] ?? 0 }}</strong></div>
            <div><span>Surat Masuk</span><strong>{{ $counts['surat_masuk'] ?? 0 }}</strong></div>
            <div><span>Surat Keluar</span><strong>{{ $counts['surat_keluar'] ?? 0 }}</strong></div>
        </div>

        <div class="orgmate-table-wrap">
            <table class="orgmate-table">
                <thead>
                    <tr>
                        <th>Dokumen</th>
                        <th>Tipe</th>
                        <th>Nomor</th>
                        <th>Tanggal</th>
                        <th>Organisasi</th>
                        <th>File</th>
                        @if (!($isDeveloper ?? false))
                            <th>Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($archives as $archive)
                        <tr>
                            <td><strong>{{ $archive->title }}</strong><small class="orgmate-field-hint">Diunggah oleh {{ $archive->uploader?->name ?? '—' }}</small></td>
                            <td><span class="badge blue">{{ ['sk' => 'SK', 'surat_masuk' => 'Surat Masuk', 'surat_keluar' => 'Surat Keluar'][$archive->type] ?? $archive->type }}</span></td>
                            <td>{{ $archive->number ?? '—' }}</td>
                            <td>{{ $archive->date ? $archive->date->locale('id')->translatedFormat('d M Y') : '—' }}</td>
                            <td>{{ $archive->organization?->name ?? '—' }}</td>
                            <td>
                                @if ($archive->file_url)
                                    <a class="orgmate-btn ghost orgmate-btn-sm" href="{{ $archive->file_url }}" target="_blank" rel="noreferrer">Lihat File</a>
                                @else
                                    <span class="orgmate-field-hint">Tanpa file</span>
                                @endif
                            </td>
                            @if (!($isDeveloper ?? false))
                                <td>
                                    <span class="orgmate-position-actions">
                                        <button type="button" class="orgmate-btn ghost orgmate-btn-sm" data-edit-open data-dialog="editArchive-{{ $archive->id }}">Edit</button>
                                        <form method="POST" action="{{ route('simora.orgmate.arsip.destroy', $archive) }}" onsubmit="return confirm('Hapus arsip {{ $archive->title }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="orgmate-btn ghost orgmate-btn-sm">Hapus</button>
                                        </form>
                                    </span>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ ($isDeveloper ?? false) ? 6 : 7 }}">
                                <div class="orgmate-empty">Belum ada arsip. Tambahkan arsip pertama melalui form di bawah.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="admin-pagination">{{ $archives->links() }}</div>
    </div>

    @if (!($isDeveloper ?? false))
        <section class="orgmate-card" aria-label="Tambah arsip">
            <div class="orgmate-card-head">
                <h2><i data-lucide="upload" class="icon"></i> Upload Dokumen</h2>
            </div>
            <form method="POST" action="{{ route('simora.orgmate.arsip.store') }}" class="orgmate-form" enctype="multipart/form-data">
                @csrf
                <div class="orgmate-form-grid">
                    <div class="orgmate-field"><label for="archiveTitle">Judul dokumen</label>
                        <input id="archiveTitle" name="title" value="{{ old('title') }}" required>
                        @error('title')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field"><label for="archiveType">Tipe dokumen</label>
                        <select id="archiveType" name="type" required>
                            <option value="sk" @selected(old('type') === 'sk')>SK Organisasi</option>
                            <option value="surat_masuk" @selected(old('type') === 'surat_masuk')>Surat Masuk</option>
                            <option value="surat_keluar" @selected(old('type') === 'surat_keluar')>Surat Keluar</option>
                        </select>
                        @error('type')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field"><label for="archiveNumber">Nomor surat <span>(opsional)</span></label>
                        <input id="archiveNumber" name="number" value="{{ old('number') }}">
                        @error('number')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field"><label for="archiveDate">Tanggal <span>(opsional)</span></label>
                        <input id="archiveDate" name="date" type="date" value="{{ old('date') }}">
                        @error('date')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field orgmate-field--wide"><label for="archiveFile">File dokumen <span>(opsional, PDF/gambar maks. 10 MB)</span></label>
                        <input id="archiveFile" name="file" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx">
                        @error('file')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                </div>
                <button type="submit" class="orgmate-btn">Simpan Arsip</button>
            </form>
        </section>

        @foreach ($archives as $archive)
            <dialog class="orgmate-dialog" id="editArchive-{{ $archive->id }}" aria-labelledby="editArchiveTitle-{{ $archive->id }}">
                <form method="dialog" class="orgmate-dialog-close-form" value="close">
                    <button type="submit" class="orgmate-icon-btn orgmate-dialog-close" aria-label="Tutup"><i data-lucide="x" class="icon" aria-hidden="true"></i></button>
                </form>
                <h2 class="orgmate-dialog-title" id="editArchiveTitle-{{ $archive->id }}">Edit Arsip</h2>
                <form method="POST" action="{{ route('simora.orgmate.arsip.update', $archive) }}" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="orgmate-form-grid">
                        <div class="orgmate-field"><label>Judul dokumen</label>
                            <input name="title" value="{{ old('title', $archive->title) }}" required>
                            @error('title')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label>Tipe dokumen</label>
                            <select name="type">
                                <option value="sk" @selected(old('type', $archive->type) === 'sk')>SK Organisasi</option>
                                <option value="surat_masuk" @selected(old('type', $archive->type) === 'surat_masuk')>Surat Masuk</option>
                                <option value="surat_keluar" @selected(old('type', $archive->type) === 'surat_keluar')>Surat Keluar</option>
                            </select>
                            @error('type')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label>Nomor surat <span>(opsional)</span></label>
                            <input name="number" value="{{ old('number', $archive->number) }}">
                            @error('number')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label>Tanggal <span>(opsional)</span></label>
                            <input name="date" type="date" value="{{ old('date', $archive->date) }}">
                            @error('date')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field orgmate-field--wide"><label>File dokumen <span>(opsional, kosongkan jika tidak diganti)</span></label>
                            <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx">
                            @error('file')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                            @if ($archive->file_url)
                                <p class="orgmate-field-hint" style="margin-top:6px">File saat ini: <a href="{{ $archive->file_url }}" target="_blank" rel="noreferrer">Lihat file</a></p>
                            @endif
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
