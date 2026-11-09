@extends('simora.ORGMATE.layout', ['title' => 'Data Anggota | ORGMATE'])
@section('breadcrumb', 'Data Anggota & Profil')

@section('content')
    <div class="orgmate-intro">
        <div>
            <p class="orgmate-kicker">Master Data</p>
            <h1>Data Anggota &amp; Profil</h1>
        </div>
    </div>
    @if ($search !== '')
        <div class="orgmate-card" style="padding:12px 16px;margin-bottom:12px">Hasil pencarian untuk
            "<strong>{{ $search }}</strong>" <a href="{{ route('simora.orgmate.anggota') }}"
                style="color:#2563EB;font-weight:700">Bersihkan</a></div>
    @endif
    @if (session('status'))
        <div class="orgmate-toast is-show" style="position:static;opacity:1;transform:none;margin-bottom:12px">{{ session('status') }}</div>
    @endif
    @if (!($isDeveloper ?? false))
        <section class="orgmate-card" aria-label="Tambah anggota" style="margin-bottom:14px">
            <div class="orgmate-card-head">
                <h2><i data-lucide="user-plus" class="icon"></i> Tambah Anggota (Mahasiswa)</h2>
            </div>
            <form method="POST" action="{{ route('simora.orgmate.anggota.store') }}" class="orgmate-form" enctype="multipart/form-data">
                @csrf
                <div class="orgmate-form-grid">
                    <div class="orgmate-field"><label for="memberMahasiswaSearch">Cari mahasiswa (nama / NIM)</label>
                        <input id="memberMahasiswaSearch" type="search" placeholder="Ketikan nama atau NIM…" autocomplete="off" data-member-search="memberMahasiswa">
                        @error('mahasiswa_id')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field"><label for="memberMahasiswaAngkatan">Angkatan</label>
                        <select id="memberMahasiswaAngkatan" data-member-angkatan="memberMahasiswa">
                            <option value="">— Semua angkatan —</option>
                            @foreach (($angkatanOptions ?? collect()) as $angkatan)
                                <option value="{{ $angkatan }}">{{ $angkatan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="orgmate-field"><label for="memberMahasiswa">Nama mahasiswa ({{ $prodiLabel ?: 'sesuai prodi organisasi' }})</label>
                        <select id="memberMahasiswa" name="mahasiswa_id">
                            <option value="">— Pilih mahasiswa —</option>
                            @if (($mahasiswaByAngkatan ?? collect())->isEmpty())
                                <option value="" disabled>Belum ada data mahasiswa untuk prodi ini</option>
                            @else
                                @foreach ($mahasiswaByAngkatan as $angkatanLabel => $list)
                                    <optgroup label="{{ $angkatanLabel }} ({{ $list->count() }} orang)">
                                        @foreach ($list as $mhs)
                                            <option value="{{ $mhs->id }}" data-angkatan="{{ $angkatanLabel }}" data-nim="{{ $mhs->nim }}" @selected(old('mahasiswa_id') == $mhs->id)>{{ $mhs->name }} — {{ $mhs->nim }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            @endif
                        </select>
                        @error('mahasiswa_id')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        @error('name')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        <small class="orgmate-field-hint">Hanya mahasiswa prodi {{ $prodiLabel ?: 'organisasi ini' }}, terpisah per angkatan. Pilih lewat pencarian agar cepat.</small>
                    </div>
                    <div class="orgmate-field"><label for="memberNim">NIM</label>
                        <input id="memberNim" name="nim" value="{{ old('nim') }}" readonly>
                        <small class="orgmate-field-hint">Terisi otomatis dari pilihan mahasiswa.</small>
                        @error('nim')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field"><label for="memberEmail">Email <span>(opsional)</span></label>
                        <input id="memberEmail" name="email" type="email" value="{{ old('email') }}">
                        @error('email')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field"><label for="memberPhone">No. HP <span>(opsional)</span></label>
                        <input id="memberPhone" name="phone" value="{{ old('phone') }}">
                        @error('phone')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field"><label for="memberDivision">Divisi <span>(opsional)</span></label>
                        <select id="memberDivision" name="division">
                            <option value="">— Tanpa divisi —</option>
                            @foreach ($divisionOptions as $divisionOption)
                                <option value="{{ $divisionOption }}" @selected(old('division') === $divisionOption)>{{ $divisionOption }}</option>
                            @endforeach
                            @if (old('division') && !in_array(old('division'), $divisionOptions->all()))
                                <option value="{{ old('division') }}" selected>{{ old('division') }}</option>
                            @endif
                        </select>
                        @error('division')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field"><label for="memberFaculty">Fakultas / Prodi <span>(opsional)</span></label>
                        <input id="memberFaculty" name="faculty" value="{{ old('faculty') }}">
                        @error('faculty')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field"><label for="memberAvatar">Foto anggota <span>(opsional, JPG/PNG/WebP maks. 2 MB)</span></label>
                        <input id="memberAvatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp">
                        @error('avatar')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                </div>
                <button type="submit" class="orgmate-btn">Simpan Anggota</button>
            </form>
        </section>
    @endif
    <div class="orgmate-card">
        <div class="orgmate-table-wrap">
            <table class="orgmate-table">
                <thead>
                    <tr>
                        <th>Anggota</th>
                        <th>NIM</th>
                        <th>Email</th>
                        <th>Kontak</th>
                        <th>Divisi</th>
                        <th>Status</th>
                        <th>Organisasi</th>
                        <th>Terdaftar</th>
                        @if (!($isDeveloper ?? false))
                            <th>Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $member)
                        <tr>
                            <td>
                                <div class="orgmate-member-cell"><span
                                        class="orgmate-avatar">@if ($member->avatar_url)<img src="{{ $member->avatar_url }}"
                                                alt="Foto {{ $member->name }}">@else{{ strtoupper(substr($member->name, 0, 1)) }}@endif</span>
                                    <div><strong>{{ $member->name }}</strong><small>ID #{{ $member->id }}</small></div>
                                </div>
                            </td>
                            <td>{{ $member->nim ?? '—' }}</td>
                            <td>{{ $member->email ?? '—' }}</td>
                            <td>{{ $member->phone ?? '—' }}<small class="orgmate-field-hint">{{ $member->faculty }}</small></td>
                            <td>{{ $member->division ?? '—' }}</td>
                            <td>
                                @if ($member->status === 'aktif')
                                <span class="badge green">Aktif</span>@else<span class="badge amber">Nonaktif</span>
                                @endif
                            </td>
                            <td>{{ $member->organization?->name ?? '—' }}</td>
                            <td>{{ $member->created_at?->locale('id')->translatedFormat('d M Y') ?? '—' }}</td>
                            @if (!($isDeveloper ?? false))
                                <td>
                                    <span class="orgmate-position-actions">
                                        <button type="button" class="orgmate-btn ghost orgmate-btn-sm"
                                            data-edit-open data-dialog="editMember-{{ $member->id }}">Edit</button>
                                        <form method="POST" action="{{ route('simora.orgmate.anggota.destroy', $member) }}" onsubmit="return confirm('Hapus anggota {{ $member->name }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="orgmate-btn ghost orgmate-btn-sm">Hapus</button>
                                        </form>
                                    </span>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ ($isDeveloper ?? false) ? 8 : 9 }}">
                                <div class="orgmate-empty">Belum ada data
                                    anggota{{ $search !== '' ? ' untuk pencarian ini' : '' }}.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="orgmate-pagination-wrap">
        @if ($members->total() > 0)
            <p class="orgmate-pagination-info">Menampilkan {{ $members->firstItem() }}–{{ $members->lastItem() }} dari {{ $members->total() }} anggota</p>
        @endif
        {{ $members->links('vendor.pagination.orgmate') }}
    </div>

    {{-- Modal edit anggota (hanya role organisasi) --}}
    @if (!($isDeveloper ?? false))
        @foreach ($members as $member)
            <dialog class="orgmate-dialog" id="editMember-{{ $member->id }}" aria-labelledby="editMemberTitle-{{ $member->id }}">
                <form method="dialog" class="orgmate-dialog-close-form" value="close">
                    <button type="submit" class="orgmate-icon-btn orgmate-dialog-close" aria-label="Tutup"><i data-lucide="x" class="icon" aria-hidden="true"></i></button>
                </form>
                <h2 class="orgmate-dialog-title" id="editMemberTitle-{{ $member->id }}">Edit Anggota</h2>
                <form method="POST" action="{{ route('simora.orgmate.anggota.update', $member) }}" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="orgmate-dialog-photo">
                        @if ($member->avatar_url)
                            <img src="{{ $member->avatar_url }}" alt="Foto {{ $member->name }}">
                        @else
                            <span>{{ strtoupper(substr($member->name, 0, 1)) }}</span>
                        @endif
                        <div>
                            <strong>{{ $member->name }}</strong>
                            <small>{{ $member->nim ?? 'Tanpa NIM' }}</small>
                        </div>
                    </div>
                    <div class="orgmate-form-grid">
                        <div class="orgmate-field"><label>Nama lengkap</label>
                            <input name="name" value="{{ old('name', $member->name) }}" required>
                            @error('name')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label>NIM</label>
                            <input name="nim" value="{{ old('nim', $member->nim) }}">
                            @error('nim')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label>Email <span>(opsional)</span></label>
                            <input name="email" type="email" value="{{ old('email', $member->email) }}">
                            @error('email')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label>No. HP <span>(opsional)</span></label>
                            <input name="phone" value="{{ old('phone', $member->phone) }}">
                            @error('phone')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label>Fakultas / Prodi <span>(opsional)</span></label>
                            <input name="faculty" value="{{ old('faculty', $member->faculty) }}">
                            @error('faculty')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label>Divisi <span>(opsional)</span></label>
                            <select name="division">
                                <option value="">— Tanpa divisi —</option>
                                @foreach ($divisionOptions as $divisionOption)
                                    <option value="{{ $divisionOption }}" @selected(old('division', $member->division) === $divisionOption)>{{ $divisionOption }}</option>
                                @endforeach
                                @if ($member->division && !in_array($member->division, $divisionOptions->all()))
                                    <option value="{{ $member->division }}" selected>{{ $member->division }}</option>
                                @endif
                            </select>
                            @error('division')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label>Status</label>
                            <select name="status">
                                <option value="aktif" @selected(old('status', $member->status) === 'aktif')>Aktif</option>
                                <option value="nonaktif" @selected(old('status', $member->status) === 'nonaktif')>Nonaktif</option>
                            </select>
                            @error('status')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label>Foto anggota <span>(opsional, maks. 2 MB)</span></label>
                            <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp">
                            @error('avatar')<small class="orgmate-field-error">{{ $message }}</small>@enderror
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
            (function () {
                var search = document.getElementById('memberMahasiswaSearch');
                var angkatanSelect = document.getElementById('memberMahasiswaAngkatan');
                var select = document.getElementById('memberMahasiswa');
                var nimInput = document.getElementById('memberNim');
                if (!select) return;

                function applyFilter() {
                    var q = search ? (search.value || '').trim().toLowerCase() : '';
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
                }

                function syncNim() {
                    var option = select.options[select.selectedIndex];
                    if (nimInput && option && option.dataset && option.dataset.nim) {
                        nimInput.value = option.dataset.nim;
                    }
                }

                if (search) search.addEventListener('input', applyFilter);
                if (angkatanSelect) angkatanSelect.addEventListener('change', applyFilter);
                select.addEventListener('change', syncNim);
            })();

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
