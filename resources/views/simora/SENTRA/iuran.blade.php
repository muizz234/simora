@extends('simora.SENTRA.layout', ['title' => 'Iuran Anggota'])

@section('content')
    <div class="sentra-head">
        <div>
            <h1>Iuran Anggota</h1>
            <p>Catat dan pantau pembayaran iuran anggota organisasi.</p>
        </div>
        <div class="sentra-head-right">
            @if(($isDeveloper ?? false) && ($organizationsList ?? collect())->count() > 0)
                <form method="GET" action="{{ route('simora.sentra.iuran') }}" class="sentra-periode">
                    <i data-lucide="building-2" class="icon"></i><label>Organisasi:</label>
                    <select name="organization" onchange="this.form.submit()">
                        <option value="">Semua Organisasi</option>
                        @foreach ($organizationsList as $org)
                            <option value="{{ $org->slug }}" {{ $selectedOrganization?->slug === $org->slug ? 'selected' : '' }}>{{ $org->name }}</option>
                        @endforeach
                    </select>
                </form>
            @endif
            @if(auth()->user()->role !== 'developer')
                <a class="orgmate-btn" href="#catat-iuran"><i data-lucide="plus" class="icon"></i> Catat Iuran</a>
            @else
                <span class="badge gray">Mode manajemen organisasi (view-only)</span>
            @endif
        </div>
    </div>

    @if (session('status'))
        <div class="orgmate-toast is-show" style="position:static;opacity:1;transform:none;margin-bottom:12px">{{ session('status') }}</div>
    @endif

    <div class="orgmate-card" style="margin-bottom:14px">
        <div class="sentra-iuran-big">
            <div><strong>{{ $percent }}%</strong>
                <p class="sentra-iuran-sub">Persentase pembayaran iuran • {{ $paid }} / {{ $total }} Anggota</p>
            </div>
            <div class="sentra-iuran-unpaid">
                @if ($total === 0)
                    Belum ada data pembayaran
                @else
                    Terkumpul <strong>Rp{{ number_format($collected, 0, ',', '.') }}</strong>
                @endif
            </div>
        </div>
        <div class="sentra-progress-big" role="progressbar" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"><i style="width:{{ $percent }}%"></i></div>
    </div>

    <div class="orgmate-card">
        <div class="orgmate-card-head">
            <h2><i data-lucide="users" class="icon"></i> Daftar Pembayaran Iuran</h2>
            <span class="badge gray">{{ $dues->total() }} tagihan</span>
        </div>
        <form method="GET" action="{{ route('simora.sentra.iuran') }}" class="sentra-toolbar" role="search">
            @if($selectedOrganization)<input type="hidden" name="organization" value="{{ $selectedOrganization->slug }}">@endif
            @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
            <label class="orgmate-search" style="margin:0;flex:1;min-width:200px">
                <i data-lucide="search" class="icon"></i>
                <input type="search" name="periode" value="{{ $period }}" placeholder="Cari periode (mis. Wajib, 2026)..."
                    aria-label="Cari periode iuran">
            </label>
            <button class="orgmate-btn ghost" type="submit"><i data-lucide="search" class="icon"></i> Cari</button>
            @if ($period !== '')
                <a class="orgmate-btn ghost" href="{{ route('simora.sentra.iuran', $selectedOrganization ? ['organization' => $selectedOrganization->slug] : []) }}">Reset</a>
            @endif
        </form>
        <div style="display:flex;gap:6px;margin-bottom:12px">
            <a class="badge {{ $status === null ? 'blue' : 'gray' }}" href="{{ route('simora.sentra.iuran', array_merge($selectedOrganization ? ['organization' => $selectedOrganization->slug] : [], $period ? ['periode' => $period] : [])) }}">Semua</a>
            <a class="badge {{ $status === 'lunas' ? 'blue' : 'gray' }}" href="{{ route('simora.sentra.iuran', array_merge($selectedOrganization ? ['organization' => $selectedOrganization->slug] : [], $period ? ['periode' => $period] : [], ['status' => 'lunas'])) }}">Lunas</a>
            <a class="badge {{ $status === 'belum_bayar' ? 'blue' : 'gray' }}" href="{{ route('simora.sentra.iuran', array_merge($selectedOrganization ? ['organization' => $selectedOrganization->slug] : [], $period ? ['periode' => $period] : [], ['status' => 'belum_bayar'])) }}">Belum Bayar</a>
        </div>
        @if ($dues->isEmpty())
            <div class="sentra-empty">Belum ada data pembayaran iuran.<br>Catat pembayaran pertama melalui form di bawah.</div>
        @else
            <div class="orgmate-table-wrap">
                <table class="orgmate-table">
                    <thead>
                        <tr>
                            <th>Anggota</th>
                            <th>Status</th>
                            <th style="text-align:right">Nominal</th>
                            <th>Tanggal Bayar</th>
                            <th>Bukti</th>
                            @if(auth()->user()->role !== 'developer')<th>Aksi</th>@endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dues as $due)
                            <tr>
                                <td class="orgmate-member-cell">
                                    <span class="orgmate-avatar orgmate-avatar-sm">{{ strtoupper(\Illuminate\Support\Str::substr(trim($due->name), 0, 1)) }}</span>
                                    <span><strong>{{ $due->name }}</strong><small class="orgmate-field-hint">{{ $due->period ?? 'Umum' }}</small></span>
                                </td>
                                <td><span class="badge {{ $due->status === 'lunas' ? 'green' : 'amber' }}">{{ $due->status === 'lunas' ? 'Lunas' : 'Belum Bayar' }}</span></td>
                                <td class="sentra-table-num">Rp{{ number_format($due->amount, 0, ',', '.') }}</td>
                                <td>{{ $due->paid_at?->locale('id')->translatedFormat('d M Y') ?? '—' }}</td>
                                <td>
                                    @if($due->proof_url)
                                        <a href="{{ $due->proof_url }}" target="_blank" rel="noopener" class="sentra-proof-thumb"><img src="{{ $due->proof_url }}" alt="Bukti iuran {{ $due->name }}" loading="lazy"></a>
                                    @else
                                        <span class="orgmate-muted">—</span>
                                    @endif
                                </td>
                                @if(auth()->user()->role !== 'developer')
                                    <td>
                                        <span class="orgmate-position-actions">
                                            @if($due->status !== 'lunas')
                                                <form method="POST" action="{{ route('simora.sentra.iuran.markPaid', $due) }}">
                                                    @csrf @method('PUT')
                                                    <button type="submit" class="orgmate-btn orgmate-btn-sm">Tandai Lunas</button>
                                                </form>
                                            @endif
                                            <button type="button" class="orgmate-btn ghost orgmate-btn-sm" data-edit-open data-dialog="editDue-{{ $due->id }}">Edit</button>
                                            <form method="POST" action="{{ route('simora.sentra.iuran.destroy', $due) }}" onsubmit="return confirm('Hapus iuran {{ $due->name }}?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="orgmate-btn ghost orgmate-btn-sm">Hapus</button>
                                            </form>
                                        </span>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="admin-pagination">{{ $dues->links() }}</div>
        @endif
        <p class="orgmate-hint">Iuran yang lunas otomatis tercatat sebagai pemasukan di Kas Organisasi.</p>
    </div>

    @if(auth()->user()->role !== 'developer')
        <div class="orgmate-card" id="catat-iuran">
            <div class="orgmate-card-head">
                <h2><i data-lucide="circle-plus" class="icon"></i> Catat Pembayaran Iuran</h2>
            </div>
            <form method="POST" action="{{ route('simora.sentra.iuran.store') }}" class="orgmate-form" enctype="multipart/form-data">
                @csrf
                <div class="orgmate-form-grid">
                    <div class="orgmate-field"><label for="dueName">Nama Anggota</label>
                        <select id="dueName" name="name" required>
                            <option value="">— Pilih Pengurus —</option>
                            @foreach ($members as $member)
                                <option value="{{ $member->name }}" @selected(old('name') === $member->name)>{{ $member->name }}</option>
                            @endforeach
                        </select>
                        @error('name')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field"><label for="dueAmount">Nominal (Rp)</label>
                        <input id="dueAmount" name="amount" type="number" min="0" step="any" value="{{ old('amount') }}" placeholder="50000" required>
                        @error('amount')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field"><label for="duePeriod">Periode <span>(opsional)</span></label>
                        <input id="duePeriod" name="period" value="{{ old('period') }}" placeholder="Wajib / 2026 ...">
                        @error('period')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field"><label for="dueStatus">Status</label>
                        <select id="dueStatus" name="status">
                            <option value="belum_bayar" @selected(old('status', 'belum_bayar') === 'belum_bayar')>Belum Bayar</option>
                            <option value="lunas" @selected(old('status') === 'lunas')>Lunas</option>
                        </select>
                        @error('status')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field"><label for="duePaidAt">Tanggal Bayar <span>(opsional)</span></label>
                        <input id="duePaidAt" name="paid_at" type="date" value="{{ old('paid_at') }}">
                        @error('paid_at')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field orgmate-field--wide"><label for="dueProof">Bukti pembayaran <span>(wajib — foto via kamera atau upload struk)</span></label>
                        <div class="proof-camera-row">
                            <input id="dueProof" name="proof" type="file" accept="image/*,.pdf" capture="environment" required>
                            <button type="button" class="orgmate-btn ghost orgmate-btn-sm" data-camera-target="dueProof">📷 Kamera</button>
                        </div>
                        @error('proof')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                </div>
                <button type="submit" class="orgmate-btn"><i data-lucide="save" class="icon"></i> Simpan Iuran</button>
            </form>
        </div>

        @foreach ($dues as $due)
            <dialog class="orgmate-dialog" id="editDue-{{ $due->id }}" aria-labelledby="editDueTitle-{{ $due->id }}">
                <form method="dialog" class="orgmate-dialog-close-form" value="close">
                    <button type="submit" class="orgmate-icon-btn orgmate-dialog-close" aria-label="Tutup"><i data-lucide="x" class="icon" aria-hidden="true"></i></button>
                </form>
                <h2 class="orgmate-dialog-title" id="editDueTitle-{{ $due->id }}">Edit Iuran</h2>
                <form method="POST" action="{{ route('simora.sentra.iuran.update', $due) }}" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="orgmate-form-grid">
                        <div class="orgmate-field"><label>Nama Anggota</label>
                            <select name="name" required>
                                <option value="">— Pilih Pengurus —</option>
                                @foreach ($members as $member)
                                    <option value="{{ $member->name }}" @selected(old('name', $due->name) === $member->name)>{{ $member->name }}</option>
                                @endforeach
                            </select>
                            @error('name')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label>Nominal (Rp)</label>
                            <input name="amount" type="number" min="0" step="any" value="{{ old('amount', $due->amount) }}" required>
                            @error('amount')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label>Periode <span>(opsional)</span></label>
                            <input name="period" value="{{ old('period', $due->period) }}">
                            @error('period')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label>Status</label>
                            <select name="status">
                                <option value="belum_bayar" @selected(old('status', $due->status) === 'belum_bayar')>Belum Bayar</option>
                                <option value="lunas" @selected(old('status', $due->status) === 'lunas')>Lunas</option>
                            </select>
                            @error('status')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label>Tanggal Bayar <span>(opsional)</span></label>
                            <input name="paid_at" type="date" value="{{ old('paid_at', $due->paid_at?->format('Y-m-d')) }}">
                            @error('paid_at')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label>Bukti baru <span>(opsional — kosongkan bila tidak mengganti)</span></label>
                            <div class="proof-camera-row">
                                <input id="due-proof-{{ $due->id }}" name="proof" type="file" accept="image/*,.pdf" capture="environment">
                                <button type="button" class="orgmate-btn ghost orgmate-btn-sm" data-camera-target="due-proof-{{ $due->id }}">📷 Kamera</button>
                            </div>
                            @error('proof')<small class="orgmate-field-error">{{ $message }}</small>@enderror
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
    @else
        <div class="orgmate-empty">Developer mengelola iuran lintas organisasi (mode view-only). Pencatatan iuran per anggota dilakukan oleh organisasi.</div>
    @endif
    @if(auth()->user()->role !== 'developer')
        @include('partials.camera-capture')
    @endif
@endsection
