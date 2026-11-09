@extends('simora.ORGMATE.layout', ['title' => 'Struktur Kepengurusan | ORGMATE'])
@section('breadcrumb', 'Struktur Kepengurusan')

@section('content')
    <div class="orgmate-intro">
        <div>
            <p class="orgmate-kicker">Master Data · Scope: {{ $orgName ?? 'Semua Organisasi' }}</p>
            <h1>Struktur Kepengurusan</h1>
            <p class="lead">Divisi dan jabatan kepengurusan periode aktif{{ ($isDeveloper ?? false) ? ' (mode monitoring developer)' : '' }}.</p>
        </div>
        <div class="orgmate-intro-actions">
            @if (($isDeveloper ?? false) && ($organizationsList ?? collect())->count() > 0)
                <form method="GET" action="{{ route('simora.orgmate.struktur') }}" class="orgmate-org-form orgmate-org-form--inline">
                    <label for="strukturOrgSelect">Lihat organisasi:</label>
                    <select id="strukturOrgSelect" name="organization" onchange="this.form.submit()">
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
        <div class="orgmate-toast is-show" style="position:static;opacity:1;transform:none;margin-bottom:14px">{{ session('status') }}</div>
    @endif

    <div class="orgmate-org-grid">
        @forelse ($positions as $position)
            <article class="orgmate-org-card" style="--org-color:#2563EB">
                <span class="orgmate-position-icon"><i data-lucide="network" class="icon"></i></span>
                <div>
                    <strong>{{ $position->name }}</strong>
                    <span>{{ $position->description ?: 'Struktur pengurus inti' }}</span>
                    <small>{{ $position->member ? 'Pemegang: '.$position->member->name : 'Pemegang jabatan belum diisi' }}</small>
                    @if (!($isDeveloper ?? false))
                        <span class="orgmate-position-actions">
                            <form method="POST" action="{{ route('simora.orgmate.struktur.destroy', $position) }}" onsubmit="return confirm('Hapus jabatan {{ $position->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="orgmate-btn ghost orgmate-btn-sm">Hapus</button>
                            </form>
                        </span>
                    @endif
                </div>
            </article>
        @empty
            <p class="orgmate-empty" style="grid-column:1/-1">Belum ada jabatan. Tambahkan jabatan pertama melalui form di bawah.</p>
        @endforelse
    </div>

    @if (!($isDeveloper ?? false))
        <section class="orgmate-card" aria-label="Tambah jabatan">
            <div class="orgmate-card-head">
                <h2><i data-lucide="plus" class="icon"></i> Tambah Jabatan</h2>
            </div>
            <form method="POST" action="{{ route('simora.orgmate.struktur.store') }}" class="orgmate-form">
                @csrf
                <div class="orgmate-form-grid">
                    <div class="orgmate-field"><label for="positionName">Nama jabatan</label>
                        <input id="positionName" name="name" value="{{ old('name') }}" required>
                        @error('name')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field"><label for="positionMemberSearch">Cari mahasiswa (nama / NIM)</label>
                        <input id="positionMemberSearch" type="search" placeholder="Ketikan nama atau NIM…" autocomplete="off"
                            data-member-search="positionMember">
                        @error('mahasiswa_id')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field"><label for="positionMemberAngkatan">Angkatan</label>
                        <select id="positionMemberAngkatan" data-member-angkatan="positionMember">
                            <option value="">— Semua angkatan —</option>
                            @foreach (($angkatanOptions ?? collect()) as $angkatan)
                                <option value="{{ $angkatan }}">{{ $angkatan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="orgmate-field"><label for="positionMember">Pemegang jabatan (mahasiswa {{ $prodiLabel ?: '' }})</label>
                        <select id="positionMember" name="mahasiswa_id">
                            <option value="">— Belum diisi —</option>
                            @if (($mahasiswaByAngkatan ?? collect())->isEmpty())
                                <option value="" disabled>Belum ada data mahasiswa untuk prodi ini</option>
                            @else
                                @foreach ($mahasiswaByAngkatan as $angkatanLabel => $list)
                                    <optgroup label="{{ $angkatanLabel }} ({{ $list->count() }} orang)">
                                        @foreach ($list as $mhs)
                                            <option value="{{ $mhs->id }}" data-angkatan="{{ $angkatanLabel }}" @selected(old('mahasiswa_id') == $mhs->id)>{{ $mhs->name }} — {{ $mhs->nim }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            @endif
                        </select>
                        @error('mahasiswa_id')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        @error('member_id')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        <small class="orgmate-field-hint">Hanya mahasiswa prodi {{ $prodiLabel ?: 'organisasi ini' }}, terpisah per angkatan. Pilih lewat pencarian agar cepat.</small>
                    </div>
                    <div class="orgmate-field orgmate-field--wide"><label for="positionDesc">Deskripsi (opsional)</label>
                        <textarea id="positionDesc" name="description" rows="2">{{ old('description') }}</textarea>
                        @error('description')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field"><label for="positionSort">Urutan tampil</label>
                        <input id="positionSort" name="sort_order" type="number" min="0" value="{{ old('sort_order', 0) }}">
                        @error('sort_order')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field"><label for="positionGroup">Kelompok tingkat bagan <span>(Pimpinan / BPI / Divisi)</span></label>
                        <select id="positionGroup" name="group_type">
                            <option value="">— Otomatis dari nama jabatan —</option>
                            @foreach (\App\Models\Position::GROUP_LABELS as $value => $label)
                                <option value="{{ $value }}" @selected(old('group_type') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('group_type')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                </div>
                <button type="submit" class="orgmate-btn">Simpan Jabatan</button>
            </form>
        </section>
    @endif

    <p class="orgmate-hint">Jabatan ini dibagikan ke E-PROGRESS untuk kepanitiaan dan ke SENTRA untuk bendahara.</p>

    @if (!($isDeveloper ?? false))
        <script>
            (function () {
                var search = document.getElementById('positionMemberSearch');
                var angkatanSelect = document.getElementById('positionMemberAngkatan');
                var select = document.getElementById('positionMember');
                if (!search || !select) return;

                function applyFilter() {
                    var q = (search.value || '').trim().toLowerCase();
                    var angkatan = angkatanSelect ? (angkatanSelect.value || '') : '';

                    select.querySelectorAll('optgroup').forEach(function (group) {
                        var groupVisible = false;
                        group.querySelectorAll('option').forEach(function (option) {
                            var matchesSearch = q === '' || option.textContent.toLowerCase().includes(q);
                            var matchesAngkatan = angkatan === '' || option.dataset.angkatan === angkatan;
                            var visible = matchesSearch && matchesAngkatan;
                            option.hidden = !visible;
                            option.disabled = !visible;
                            if (visible) groupVisible = true;
                        });
                        group.style.display = groupVisible ? '' : 'none';
                    });

                    // Placeholder tidak ikut difilter.
                    select.querySelectorAll('option:not([data-angkatan])').forEach(function (option) {
                        option.hidden = false;
                    });
                }

                search.addEventListener('input', applyFilter);
                if (angkatanSelect) angkatanSelect.addEventListener('change', applyFilter);
            })();
        </script>
    @endif
@endsection
