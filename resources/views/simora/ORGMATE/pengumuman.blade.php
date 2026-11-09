@extends('simora.ORGMATE.layout', ['title' => 'Pengumuman Internal | ORGMATE'])
@section('breadcrumb', 'Pengumuman Internal')

@section('content')
    <div class="orgmate-intro">
        <div>
            <p class="orgmate-kicker">Pengumuman Internal · Scope: {{ $orgName ?? 'Semua Organisasi' }}</p>
            <h1>Pengumuman Internal</h1>
            <p class="lead">Pengumuman hanya untuk pengurus organisasi{{ ($isDeveloper ?? false) ? ' (mode monitoring developer)' : '' }}.</p>
        </div>
        <div class="orgmate-intro-actions">
            @if (($isDeveloper ?? false) && ($organizationsList ?? collect())->count() > 0)
                <form method="GET" action="{{ route('simora.orgmate.pengumuman') }}" class="orgmate-org-form orgmate-org-form--inline">
                    <label for="pengumumanOrgSelect">Lihat organisasi:</label>
                    <select id="pengumumanOrgSelect" name="organization" onchange="this.form.submit()">
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

    <div class="orgmate-list">
        @forelse ($announcements as $announcement)
            <article class="orgmate-post">
                <span class="orgmate-post-icon"><i data-lucide="megaphone"></i></span>
                <div style="flex:1;min-width:0">
                    <h3>{{ $announcement->title }}</h3>
                    <p style="white-space:pre-line">{{ $announcement->content }}</p>
                    <div class="orgmate-post-meta">
                        <span class="orgmate-period">{{ $announcement->organization?->name ?? '—' }}</span>
                        <span class="badge gray">{{ $announcement->published_at ? $announcement->published_at->locale('id')->translatedFormat('d M Y') : $announcement->created_at->locale('id')->translatedFormat('d M Y') }}</span>
                        <span class="badge blue">Oleh {{ $announcement->creator?->name ?? '—' }}</span>
                    </div>
                    @if (!($isDeveloper ?? false))
                        <div class="orgmate-position-actions" style="margin-top:8px">
                            <button type="button" class="orgmate-btn ghost orgmate-btn-sm" data-edit-open data-dialog="editAnnouncement-{{ $announcement->id }}">Edit</button>
                            <form method="POST" action="{{ route('simora.orgmate.pengumuman.destroy', $announcement) }}" onsubmit="return confirm('Hapus pengumuman {{ $announcement->title }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="orgmate-btn ghost orgmate-btn-sm">Hapus</button>
                            </form>
                        </div>
                    @endif
                </div>
            </article>
        @empty
            <div class="orgmate-empty">Belum ada pengumuman internal.</div>
        @endforelse
    </div>
    <div class="admin-pagination">{{ $announcements->links() }}</div>

    @if (!($isDeveloper ?? false))
        <section class="orgmate-card" aria-label="Buat pengumuman">
            <div class="orgmate-card-head">
                <h2><i data-lucide="plus"></i> Buat Pengumuman</h2>
            </div>
            <form method="POST" action="{{ route('simora.orgmate.pengumuman.store') }}" class="orgmate-form">
                @csrf
                <div class="orgmate-form-grid">
                    <div class="orgmate-field orgmate-field--wide"><label for="announcementTitle">Judul pengumuman</label>
                        <input id="announcementTitle" name="title" value="{{ old('title') }}" required>
                        @error('title')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field orgmate-field--wide"><label for="announcementContent">Isi pengumuman</label>
                        <textarea id="announcementContent" name="content" rows="4" required>{{ old('content') }}</textarea>
                        @error('content')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field"><label for="announcementPublished">Tanggal terbit <span>(kosongkan = hari ini)</span></label>
                        <input id="announcementPublished" name="published_at" type="date" value="{{ old('published_at') }}">
                        @error('published_at')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                </div>
                <button type="submit" class="orgmate-btn">Terbitkan Pengumuman</button>
            </form>
        </section>

        @foreach ($announcements as $announcement)
            <dialog class="orgmate-dialog" id="editAnnouncement-{{ $announcement->id }}" aria-labelledby="editAnnouncementTitle-{{ $announcement->id }}">
                <form method="dialog" class="orgmate-dialog-close-form" value="close">
                    <button type="submit" class="orgmate-icon-btn orgmate-dialog-close" aria-label="Tutup">✕</button>
                </form>
                <h2 class="orgmate-dialog-title" id="editAnnouncementTitle-{{ $announcement->id }}">Edit Pengumuman</h2>
                <form method="POST" action="{{ route('simora.orgmate.pengumuman.update', $announcement) }}">
                    @csrf @method('PUT')
                    <div class="orgmate-form-grid">
                        <div class="orgmate-field orgmate-field--wide"><label>Judul pengumuman</label>
                            <input name="title" value="{{ old('title', $announcement->title) }}" required>
                            @error('title')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field orgmate-field--wide"><label>Isi pengumuman</label>
                            <textarea name="content" rows="4" required>{{ old('content', $announcement->content) }}</textarea>
                            @error('content')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label>Tanggal terbit <span>(kosongkan = hari ini)</span></label>
                            <input name="published_at" type="date" value="{{ old('published_at', $announcement->published_at) }}">
                            @error('published_at')<small class="orgmate-field-error">{{ $message }}</small>@enderror
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
