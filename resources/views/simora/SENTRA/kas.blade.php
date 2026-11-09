@extends('simora.SENTRA.layout', ['title' => 'Kas Organisasi'])

@section('content')
    <div class="sentra-head">
        <div>
            <h1>Kas Organisasi</h1>
            <p>Kelola pemasukan dan pengeluaran organisasi dengan transparan.</p>
        </div>
        <div class="sentra-head-right">
            @if(($isDeveloper ?? false) && ($organizationsList ?? collect())->count() > 0)
                <form method="GET" action="{{ route('simora.sentra.kas') }}" class="sentra-periode">
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
                <a class="orgmate-btn" href="#tambah-transaksi"><i data-lucide="plus" class="icon"></i> Tambah Transaksi</a>
            @else
                <span class="badge gray">Mode manajemen organisasi (view-only)</span>
            @endif
        </div>
    </div>

    @if (session('status'))
        <div class="orgmate-toast is-show" style="position:static;opacity:1;transform:none;margin-bottom:12px">{{ session('status') }}</div>
    @endif

    <div class="sentra-summary-grid">
        <article class="sentra-stat sentra-stat--blue"><span class="sentra-stat-icon"><i data-lucide="wallet" class="icon"></i></span>
            <div><span>Saldo Kas</span><strong>Rp{{ number_format($totalPemasukan - $totalPengeluaran, 0, ',', '.') }}</strong><small>Pemasukan − Pengeluaran</small></div>
        </article>
        <article class="sentra-stat sentra-stat--green"><span class="sentra-stat-icon"><i data-lucide="circle-arrow-up" class="icon"></i></span>
            <div><span>Pemasukan</span><strong>Rp{{ number_format($totalPemasukan, 0, ',', '.') }}</strong><small>Total semua transaksi</small></div>
        </article>
        <article class="sentra-stat sentra-stat--red"><span class="sentra-stat-icon"><i data-lucide="circle-arrow-down" class="icon"></i></span>
            <div><span>Pengeluaran</span><strong>Rp{{ number_format($totalPengeluaran, 0, ',', '.') }}</strong><small>Total semua transaksi</small></div>
        </article>
        <article class="sentra-stat sentra-stat--purple"><span class="sentra-stat-icon"><i data-lucide="calculator" class="icon"></i></span>
            <div><span>Total Transaksi</span><strong>{{ $transaksi->total() }}</strong><small>Semua sumber kas</small></div>
        </article>
    </div>

    <div class="orgmate-card">
        <div class="orgmate-card-head">
            <h2><i data-lucide="list" class="icon"></i> Daftar Transaksi</h2>
            <span class="badge gray">{{ $transaksi->total() }} transaksi</span>
        </div>
        <form method="GET" action="{{ route('simora.sentra.kas') }}" class="sentra-toolbar" role="search">
            @if($selectedOrganization)<input type="hidden" name="organization" value="{{ $selectedOrganization->slug }}">@endif
            <label class="orgmate-search" style="margin:0;flex:1;min-width:200px">
                <i data-lucide="search" class="icon"></i>
                <input type="search" name="q" value="{{ $q }}" placeholder="Cari judul atau kategori..."
                    aria-label="Cari transaksi">
            </label>
            <button class="orgmate-btn ghost" type="submit"><i data-lucide="search" class="icon"></i> Cari</button>
            @if ($q !== '')
                <a class="orgmate-btn ghost" href="{{ route('simora.sentra.kas', $selectedOrganization ? ['organization' => $selectedOrganization->slug] : []) }}">Reset</a>
            @endif
        </form>
        @if ($transaksi->isEmpty())
            <div class="sentra-empty">
                @if ($q !== '')
                    Belum ada transaksi yang cocok dengan pencarian "{{ $q }}".
                @else
                    Belum ada transaksi kas.
                @endif
                <br>Transaksi pemasukan dan pengeluaran akan tampil di sini.
            </div>
        @else
            <div class="orgmate-table-wrap">
                <table class="orgmate-table">
                    <thead>
                        <tr>
                            <th>Transaksi</th>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th style="text-align:right">Nominal</th>
                            <th>Bukti</th>
                            @if(auth()->user()->role !== 'developer')<th>Aksi</th>@endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transaksi as $transaction)
                            <tr>
                                <td class="orgmate-member-cell">
                                    <span class="sentra-tx-ico {{ $transaction->type === 'pemasukan' ? 'sentra-tx-ico--masuk' : 'sentra-tx-ico--keluar' }}"><i data-lucide="{{ $transaction->type === 'pemasukan' ? 'arrow-down' : 'arrow-up' }}" class="icon"></i></span>
                                    <span><strong>{{ $transaction->title }}</strong><small class="orgmate-field-hint">{{ $transaction->category ?? 'Umum' }}</small></span>
                                </td>
                                <td>{{ $transaction->date?->locale('id')->translatedFormat('d M Y') ?? '—' }}</td>
                                <td><span class="badge {{ $transaction->type === 'pemasukan' ? 'green' : 'red' }}">{{ ucfirst($transaction->type) }}</span></td>
                                <td class="sentra-table-num" style="font-weight:800;color:{{ $transaction->type === 'pemasukan' ? '#16a34a' : '#dc2626' }}">
                                    {{ $transaction->type === 'pemasukan' ? '+ Rp' : '- Rp' }}{{ number_format($transaction->amount, 0, ',', '.') }}
                                </td>
                                <td>
                                    @if($transaction->proof_url)
                                        <a href="{{ $transaction->proof_url }}" target="_blank" rel="noopener" class="sentra-proof-thumb"><img src="{{ $transaction->proof_url }}" alt="Bukti {{ $transaction->title }}" loading="lazy"></a>
                                    @else
                                        <span class="orgmate-muted">—</span>
                                    @endif
                                </td>
                                @if(auth()->user()->role !== 'developer')
                                    <td>
                                        <span class="orgmate-position-actions">
                                            <button type="button" class="orgmate-btn ghost orgmate-btn-sm" data-edit-open data-dialog="editTx-{{ $transaction->id }}">Edit</button>
                                            <form method="POST" action="{{ route('simora.sentra.kas.destroy', $transaction) }}" onsubmit="return confirm('Hapus transaksi {{ $transaction->title }}?')">
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
            <div class="admin-pagination">{{ $transaksi->links() }}</div>
        @endif
    </div>

    @if(auth()->user()->role !== 'developer')
        <div class="orgmate-card" id="tambah-transaksi">
            <div class="orgmate-card-head">
                <h2><i data-lucide="circle-plus" class="icon"></i> Tambah Transaksi</h2>
            </div>
            <form method="POST" action="{{ route('simora.sentra.kas.store') }}" class="orgmate-form" enctype="multipart/form-data">
                @csrf
                <div class="orgmate-form-grid">
                    <div class="orgmate-field orgmate-field--wide"><label for="txTitle">Keterangan</label>
                        <input id="txTitle" name="title" value="{{ old('title') }}" placeholder="Contoh: Dana sponsorship" required>
                        @error('title')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field"><label for="txType">Jenis</label>
                        <select id="txType" name="type">
                            <option value="pemasukan" @selected(old('type', 'pemasukan') === 'pemasukan')>Pemasukan</option>
                            <option value="pengeluaran" @selected(old('type') === 'pengeluaran')>Pengeluaran</option>
                        </select>
                        @error('type')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field"><label for="txDate">Tanggal</label>
                        <input id="txDate" name="date" type="date" value="{{ old('date', now()->format('Y-m-d')) }}" required>
                        @error('date')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field"><label for="txAmount">Nominal (Rp)</label>
                        <input id="txAmount" name="amount" type="number" min="0" step="any" value="{{ old('amount') }}" placeholder="100000" required>
                        @error('amount')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field"><label for="txCategory">Kategori <span>(opsional)</span></label>
                        <input id="txCategory" name="category" value="{{ old('category') }}" list="categorySuggestions" placeholder="Sponsorship, Iuran, Operasional...">
                        <datalist id="categorySuggestions">
                            @foreach (['Sponsorship','Iuran Anggota','Operasional','Kegiatan','Aset','Lainnya'] as $suggestion)<option value="{{ $suggestion }}">@endforeach
                        </datalist>
                        @error('category')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field orgmate-field--wide"><label for="txProof">Bukti transaksi <span>(wajib — foto via kamera atau upload struk)</span></label>
                        <div class="proof-camera-row">
                            <input id="txProof" name="proof" type="file" accept="image/*,.pdf" capture="environment" required>
                            <button type="button" class="orgmate-btn ghost orgmate-btn-sm" data-camera-target="txProof">📷 Kamera</button>
                        </div>
                        @error('proof')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                </div>
                <button type="submit" class="orgmate-btn"><i data-lucide="save" class="icon"></i> Simpan Transaksi</button>
            </form>
        </div>

        @foreach ($transaksi as $transaction)
            <dialog class="orgmate-dialog" id="editTx-{{ $transaction->id }}" aria-labelledby="editTxTitle-{{ $transaction->id }}">
                <form method="dialog" class="orgmate-dialog-close-form" value="close">
                    <button type="submit" class="orgmate-icon-btn orgmate-dialog-close" aria-label="Tutup"><i data-lucide="x" class="icon" aria-hidden="true"></i></button>
                </form>
                <h2 class="orgmate-dialog-title" id="editTxTitle-{{ $transaction->id }}">Edit Transaksi</h2>
                <form method="POST" action="{{ route('simora.sentra.kas.update', $transaction) }}" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="orgmate-form-grid">
                        <div class="orgmate-field orgmate-field--wide"><label>Keterangan</label>
                            <input name="title" value="{{ old('title', $transaction->title) }}" required>
                            @error('title')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label>Jenis</label>
                            <select name="type">
                                <option value="pemasukan" @selected(old('type', $transaction->type) === 'pemasukan')>Pemasukan</option>
                                <option value="pengeluaran" @selected(old('type', $transaction->type) === 'pengeluaran')>Pengeluaran</option>
                            </select>
                            @error('type')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label>Tanggal</label>
                            <input name="date" type="date" value="{{ old('date', $transaction->date?->format('Y-m-d')) }}" required>
                            @error('date')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label>Nominal (Rp)</label>
                            <input name="amount" type="number" min="0" step="any" value="{{ old('amount', $transaction->amount) }}" required>
                            @error('amount')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label>Kategori <span>(opsional)</span></label>
                            <input name="category" value="{{ old('category', $transaction->category) }}" list="categorySuggestions">
                            @error('category')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label>Bukti baru <span>(opsional — kosongkan bila tidak mengganti)</span></label>
                            <div class="proof-camera-row">
                                <input id="proof-{{ $transaction->id }}" name="proof" type="file" accept="image/*,.pdf" capture="environment">
                                <button type="button" class="orgmate-btn ghost orgmate-btn-sm" data-camera-target="proof-{{ $transaction->id }}">📷 Kamera</button>
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
        <div class="orgmate-empty">Developer mengelola kas lintas organisasi (mode view-only). Penambahan transaksi dilakukan oleh pengurus organisasi.</div>
    @endif
    @if(auth()->user()->role !== 'developer')
        @include('partials.camera-capture')
    @endif
@endsection
