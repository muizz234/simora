@extends('simora.E-PROGRESS.layout', ['title' => 'Dokumentasi Kegiatan | E-PROGRESS'])

@section('content')
    <div class="orgmate-intro">
        <div>
            <p class="orgmate-kicker">Dokumentasi Kegiatan · Scope: {{ $orgName ?? 'Semua Organisasi' }}</p>
            <h1>Dokumentasi Kegiatan</h1>
            <p class="lead">Simpan foto, video, dan dokumen setiap kegiatan{{ ($isDeveloper ?? false) ? ' (mode monitoring developer)' : '' }}.</p>
        </div>
        <div class="orgmate-intro-actions">
            @if (($isDeveloper ?? false) && ($organizationsList ?? collect())->count() > 0)
                <form method="GET" action="{{ route('simora.e-progress.dokumentasi') }}" class="orgmate-org-form orgmate-org-form--inline">
                    <label for="dokOrgSelect">Lihat organisasi:</label>
                    <select id="dokOrgSelect" name="organization" onchange="this.form.submit()">
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
                <a href="{{ route('simora.e-progress.kalender') }}">Kalender Kegiatan</a>, lalu dokumentasikan di sini.
            </div>
        </div>
    @else
        <div class="eprogress-stats eprogress-stats-3">
            <article class="eprogress-stat eprogress-stat--pastel-blue"><span class="eprogress-stat-icon"><i data-lucide="folder-open" class="icon"></i></span><div><span>Total Dokumentasi</span><strong>{{ $stats['totalDokumen'] }}</strong><small>Pada kegiatan terpilih</small></div></article>
            <article class="eprogress-stat eprogress-stat--pastel-green"><span class="eprogress-stat-icon"><i data-lucide="image" class="icon"></i></span><div><span>Foto</span><strong>{{ $stats['foto'] }}</strong><small>Galeri kegiatan</small></div></article>
            <article class="eprogress-stat eprogress-stat--pastel-purple"><span class="eprogress-stat-icon"><i data-lucide="video" class="icon"></i></span><div><span>Video</span><strong>{{ $stats['video'] }}</strong><small>Rekaman kegiatan</small></div></article>
        </div>

        <div class="orgmate-card">
            <div class="orgmate-card-head">
                <h2><i data-lucide="flag" class="icon"></i> Pilih Kegiatan</h2>
                <form method="GET" action="{{ route('simora.e-progress.dokumentasi') }}" class="orgmate-org-form orgmate-org-form--inline eprogress-panitia-select">
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
                            <span class="badge gray">{{ $documentations->count() }} dokumentasi</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="orgmate-card">
            <div class="orgmate-card-head">
                <h2><i data-lucide="images" class="icon"></i> Galeri Dokumentasi</h2>
                <span class="badge gray">{{ $documentations->count() }} berkas · {{ $documentations->where('type', 'foto')->count() }} foto</span>
            </div>
            @php $galleryPhotos = $documentations->where(fn ($doc) => $doc->is_image); $otherFiles = $documentations->reject(fn ($doc) => $doc->is_image); @endphp
            @if($galleryPhotos->isNotEmpty())
                <div class="eprogress-gallery">
                    @foreach ($galleryPhotos as $documentation)
                        <figure class="eprogress-gallery-item">
                            <a href="{{ $documentation->file_url }}" target="_blank" rel="noopener">
                                <img src="{{ $documentation->file_url }}" alt="{{ $documentation->title }}" loading="lazy">
                            </a>
                            <figcaption>{{ $documentation->title }}</figcaption>
                            <div class="orgmate-position-actions eprogress-gallery-actions">
                                <a class="orgmate-btn ghost orgmate-btn-sm" href="{{ $documentation->file_url }}" target="_blank" rel="noopener">Buka</a>
                                @if(!($isDeveloper ?? false))
                                    <button type="button" class="orgmate-btn ghost orgmate-btn-sm" data-edit-open data-dialog="editDoc-{{ $documentation->id }}">Edit</button>
                                    <form method="POST" action="{{ route('simora.e-progress.dokumentasi.destroy', $documentation) }}" onsubmit="return confirm('Hapus dokumentasi {{ $documentation->title }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="orgmate-btn ghost orgmate-btn-sm">Hapus</button>
                                    </form>
                                @endif
                            </div>
                        </figure>
                    @endforeach
                </div>
            @endif
            @if($otherFiles->isNotEmpty())
                @foreach ($otherFiles as $documentation)
                    <div class="orgmate-post">
                        <span class="orgmate-post-icon"><i data-lucide="{{ $documentation->type === 'video' ? 'video' : 'file-text' }}" class="icon"></i></span>
                        <div style="flex:1;min-width:0">
                            <h3>{{ $documentation->title }}</h3>
                            <p>{{ $documentation->created_at?->locale('id')->translatedFormat('d M Y · H:i') ?? '—' }}</p>
                            <div class="orgmate-post-meta">
                                <span class="badge blue">{{ ucfirst($documentation->type) }}</span>
                                @if($documentation->uploader)<span class="badge gray">oleh {{ $documentation->uploader->name }}</span>@endif
                            </div>
                            <div class="orgmate-position-actions" style="margin-top:8px">
                                @if($documentation->file_url)
                                    <a class="orgmate-btn ghost orgmate-btn-sm" href="{{ $documentation->file_url }}" target="_blank" rel="noopener">Buka File</a>
                                    <a class="orgmate-btn ghost orgmate-btn-sm" href="{{ $documentation->file_url }}" download>Unduh</a>
                                @endif
                                @if(!($isDeveloper ?? false))
                                    <button type="button" class="orgmate-btn ghost orgmate-btn-sm" data-edit-open data-dialog="editDoc-{{ $documentation->id }}">Edit</button>
                                    <form method="POST" action="{{ route('simora.e-progress.dokumentasi.destroy', $documentation) }}" onsubmit="return confirm('Hapus dokumentasi {{ $documentation->title }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="orgmate-btn ghost orgmate-btn-sm">Hapus</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
            @if($documentations->isEmpty())
                <div class="orgmate-empty">Belum ada dokumentasi untuk kegiatan ini. Unggah foto atau dokumen melalui form di bawah — bisa beberapa berkas sekaligus.</div>
            @endif
        </div>

        @if(!($isDeveloper ?? false))
            <section class="orgmate-card" aria-label="Unggah dokumentasi">
                <div class="orgmate-card-head"><h2><i data-lucide="upload" class="icon"></i> Unggah Dokumentasi</h2></div>
                <form method="POST" action="{{ route('simora.e-progress.dokumentasi.store') }}" class="orgmate-form" enctype="multipart/form-data">
                    @csrf
                    <div class="orgmate-form-grid">
                        <div class="orgmate-field"><label for="docActivity">Kegiatan</label>
                            <select id="docActivity" name="activity_id">
                                @foreach ($activities as $activity)
                                    <option value="{{ $activity->id }}" @selected(old('activity_id', $selectedActivity?->id) == $activity->id)>{{ $activity->name }}</option>
                                @endforeach
                            </select>
                            @error('activity_id')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label for="docTitle">Judul</label>
                            <input id="docTitle" name="title" value="{{ old('title') }}" required>
                            @error('title')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label for="docType">Jenis</label>
                            <select id="docType" name="type">
                                @foreach (['foto', 'video', 'dokumen', 'lainnya'] as $type)
                                    <option value="{{ $type }}" @selected(old('type') === $type)>{{ ucfirst($type) }}</option>
                                @endforeach
                            </select>
                            @error('type')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label for="docFile">Berkas <span>(boleh pilih beberapa)</span></label>
                            <input id="docFile" name="files[]" type="file" multiple accept=".pdf,.jpg,.jpeg,.png,.webp,.mp4,.mov,.doc,.docx" required>
                            <small class="orgmate-field-hint">Foto (JPG/PNG/WebP), video (MP4/MOV), atau dokumen (PDF/DOC/DOCX) maks. 20 MB per berkas, maksimal 10 berkas.</small>
                            @error('files')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                            @error('files.*')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    <button type="submit" class="orgmate-btn">Unggah Dokumentasi</button>
                </form>
            </section>

            @foreach ($documentations as $documentation)
                <dialog class="orgmate-dialog" id="editDoc-{{ $documentation->id }}" aria-labelledby="editDocTitle-{{ $documentation->id }}">
                    <form method="dialog" class="orgmate-dialog-close-form" value="close">
                        <button type="submit" class="orgmate-icon-btn orgmate-dialog-close" aria-label="Tutup"><i data-lucide="x" class="icon" aria-hidden="true"></i></button>
                    </form>
                    <h2 class="orgmate-dialog-title" id="editDocTitle-{{ $documentation->id }}">Edit Dokumentasi</h2>
                    <form method="POST" action="{{ route('simora.e-progress.dokumentasi.update', $documentation) }}" enctype="multipart/form-data">
                        @csrf @method('PUT')
                        <div class="orgmate-form-grid">
                            <div class="orgmate-field"><label>Judul</label>
                                <input name="title" value="{{ old('title', $documentation->title) }}" required>
                                @error('title')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                            </div>
                            <div class="orgmate-field"><label>Jenis</label>
                                <select name="type">
                                    @foreach (['foto', 'video', 'dokumen', 'lainnya'] as $type)
                                        <option value="{{ $type }}" @selected(old('type', $documentation->type) === $type)>{{ ucfirst($type) }}</option>
                                    @endforeach
                                </select>
                                @error('type')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                            </div>
                            <div class="orgmate-field"><label>Berkas baru <span>(opsional)</span></label>
                                <input name="file" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp,.mp4,.mov,.doc,.docx">
                                <small class="orgmate-field-hint">Kosongkan bila tidak mengganti berkas.</small>
                                @error('file')<small class="orgmate-field-error">{{ $message }}</small>@enderror
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
