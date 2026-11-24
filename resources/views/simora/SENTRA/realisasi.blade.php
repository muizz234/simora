@extends('simora.SENTRA.layout', ['title' => 'Realisasi Anggaran'])

@section('content')
    <div class="sentra-head">
        <div>
            <h1>Realisasi Anggaran</h1>
            <p>Bandikan anggaran RAB dengan realisasi pengeluaran setiap proker, lengkap dengan bukti LPJ dari E-Progress.</p>
        </div>
        @if(($isDeveloper ?? false) && ($organizationsList ?? collect())->count() > 0)
            <form method="GET" action="{{ route('simora.sentra.realisasi') }}" class="sentra-periode">
                <i data-lucide="building-2" class="icon"></i><label>Organisasi:</label>
                <select name="organization" onchange="this.form.submit()">
                    <option value="">Semua Organisasi</option>
                    @foreach ($organizationsList as $org)
                        <option value="{{ $org->slug }}" {{ $selectedOrganization?->slug === $org->slug ? 'selected' : '' }}>{{ $org->name }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="periode" value="{{ $year }}">
            </form>
        @endif
        <form method="GET" action="{{ route('simora.sentra.realisasi') }}" class="sentra-periode">
            <i data-lucide="calendar-days" class="icon"></i><label>Periode:</label>
            <select name="periode" onchange="this.form.submit()">
                @for($y = max(2025, (int) now()->format('Y') - 1); $y <= (int) now()->format('Y') + 2; $y++)
                    <option value="{{ $y }}" {{ $year === $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            @if($selectedOrganization)<input type="hidden" name="organization" value="{{ $selectedOrganization->slug }}">@endif
        </form>
    </div>

    @if (session('status'))
        <div class="orgmate-toast is-show" style="position:static;opacity:1;transform:none;margin-bottom:12px">{{ session('status') }}</div>
    @endif

    <div class="sentra-summary-grid">
        <article class="sentra-stat sentra-stat--blue"><span class="sentra-stat-icon"><i data-lucide="calculator" class="icon"></i></span>
            <div><span>Total Anggaran</span><strong>Rp{{ number_format($totalAnggaran, 0, ',', '.') }}</strong><small>Periode {{ $year }}</small></div>
        </article>
        <article class="sentra-stat sentra-stat--green"><span class="sentra-stat-icon"><i data-lucide="wallet" class="icon"></i></span>
            <div><span>Total Realisasi</span><strong>Rp{{ number_format($totalRealisasi, 0, ',', '.') }}</strong><small>Sudah terpakai {{ $averagePercent }}%</small></div>
        </article>
        <article class="sentra-stat sentra-stat--red"><span class="sentra-stat-icon"><i data-lucide="circle-arrow-down" class="icon"></i></span>
            <div><span>Sisa Anggaran</span><strong>Rp{{ number_format($totalAnggaran - $totalRealisasi, 0, ',', '.') }}</strong><small>Belum terpakai</small></div>
        </article>
        <article class="sentra-stat sentra-stat--purple"><span class="sentra-stat-icon"><i data-lucide="receipt-text" class="icon"></i></span>
            <div><span>Dokumen LPJ</span><strong>{{ $lpjs->count() }}</strong><small>Terhubung dari E-Progress</small></div>
        </article>
    </div>

    <div class="orgmate-card">
        <div class="orgmate-card-head">
            <h2><i data-lucide="chart-column" class="icon"></i> Grafik Realisasi per Proker</h2><span class="badge gray">Tahun {{ $year }}</span>
        </div>
        @if ($budgets->isEmpty())
            <div class="orgmate-empty">Belum ada data realisasi anggaran. Grafik akan terisi setelah anggaran dan pengeluaran tercatat.</div>
        @endif
        <div class="sentra-chart-box" @if($budgets->isEmpty()) style="display:none" @endif><canvas id="chartRealisasi" aria-label="Grafik realisasi anggaran"></canvas></div>
    </div>

    <div class="orgmate-card">
        <div class="orgmate-card-head">
            <h2><i data-lucide="receipt-text" class="icon"></i> Rincian Realisasi</h2><a href="{{ route('simora.sentra.rab', ['periode' => $year]) }}">Lihat RAB <i data-lucide="arrow-right" class="icon"></i></a>
        </div>
        @if ($budgets->isEmpty())
            <div class="orgmate-empty">Belum ada rincian realisasi anggaran.</div>
        @else
            <div class="orgmate-table-wrap">
                <table class="orgmate-table">
                    <thead>
                        <tr>
                            <th>Program Kerja</th>
                            <th style="text-align:right">Anggaran</th>
                            <th style="text-align:right">Realisasi</th>
                            <th>Persentase</th>
                            <th>Bukti LPJ (E-Progress)</th>
                            @if(auth()->user()->role !== 'developer')<th>Catat Realisasi</th>@endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($budgets as $budget)
                            <tr>
                                <td><strong class="sentra-strong">{{ $budget->program_name }}</strong></td>
                                <td class="sentra-table-num">Rp{{ number_format($budget->allocated, 0, ',', '.') }}</td>
                                <td class="sentra-table-num">Rp{{ number_format($budget->realized, 0, ',', '.') }}</td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:8px;min-width:140px">
                                        <div class="orgmate-progress" style="flex:1;margin:0"><i style="width:{{ min($budget->percentage, 100) }}%"></i></div>
                                        <strong class="sentra-strong" style="font-size:12px">{{ rtrim(rtrim(number_format($budget->percentage, 2), '0'), '.') }}%</strong>
                                    </div>
                                </td>
                                <td>
                                    @php $programLpjs = $budget->work_program_id ? ($lpjsByProgram[$budget->work_program_id] ?? collect()) : collect(); @endphp
                                    @if ($programLpjs->isNotEmpty())
                                        @foreach ($programLpjs as $lpj)
                                            <a class="badge green" href="{{ route('simora.e-progress.evaluasi', $lpj->activity ? ['activity' => $lpj->activity->id] : []) }}" title="Lihat LPJ di E-Progress">{{ $lpj->title }}</a>
                                        @endforeach
                                    @else
                                        <span class="orgmate-muted">Belum ada LPJ</span>
                                    @endif
                                </td>
                                @if(auth()->user()->role !== 'developer')
                                    <td>
                                        <button type="button" class="orgmate-btn ghost orgmate-btn-sm" data-edit-open data-dialog="addReal-{{ $budget->id }}"><i data-lucide="plus" class="icon"></i> Catat</button>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        <p class="orgmate-hint">Realisasi berasal dari transaksi pengeluaran kas organisasi. Dokumen LPJ tertaut otomatis dari menu Evaluasi &amp; LPJ di E-Progress per program kerja.</p>
    </div>

    <div class="orgmate-card">
        <div class="orgmate-card-head">
            <h2><i data-lucide="file-text" class="icon"></i> Dokumen LPJ dari E-Progress</h2>
            <a href="{{ route('simora.e-progress.evaluasi') }}">Buka Evaluasi &amp; LPJ <i data-lucide="arrow-right" class="icon"></i></a>
        </div>
        @forelse ($lpjs as $lpj)
            <div class="orgmate-post">
                <span class="orgmate-post-icon"><i data-lucide="file-text" class="icon"></i></span>
                <div style="flex:1;min-width:0">
                    <h3>{{ $lpj->title }}</h3>
                    <p>{{ \Illuminate\Support\Str::limit($lpj->content ?: 'Tanpa isi.', 140) }}</p>
                    <div class="orgmate-post-meta">
                        <span class="badge green">LPJ</span>
                        @if($lpj->activity)<span class="badge blue">{{ $lpj->activity->name }}</span>@endif
                        @if($lpj->creator)<span class="badge gray">oleh {{ $lpj->creator->name }}</span>@endif
                        <span class="badge gray">{{ $lpj->status === 'selesai' ? 'Selesai' : 'Draft' }}</span>
                        <span class="badge gray">{{ $lpj->createdAt?->locale('id')->translatedFormat('d M Y') ?? '—' }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="orgmate-empty">Belum ada LPJ. Buat Laporan Pertanggungjawaban di <a href="{{ route('simora.e-progress.evaluasi') }}">Evaluasi &amp; LPJ E-Progress</a> — dokumennya otomatis tertaut ke sini.</div>
        @endforelse
    </div>

    @if(auth()->user()->role !== 'developer')
        @foreach ($budgets as $budget)
            <dialog class="orgmate-dialog" id="addReal-{{ $budget->id }}" aria-labelledby="addRealTitle-{{ $budget->id }}">
                <form method="dialog" class="orgmate-dialog-close-form" value="close">
                    <button type="submit" class="orgmate-icon-btn orgmate-dialog-close" aria-label="Tutup"><i data-lucide="x" class="icon" aria-hidden="true"></i></button>
                </form>
                <h2 class="orgmate-dialog-title" id="addRealTitle-{{ $budget->id }}">Catat Realisasi — {{ $budget->program_name }}</h2>
                <form method="POST" action="{{ route('simora.sentra.realisasi.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="budget_id" value="{{ $budget->id }}">
                    <div class="orgmate-form-grid">
                        <div class="orgmate-field"><label>Keterangan <span>(opsional)</span></label>
                            <input name="description" placeholder="Contoh: Sewa ruangan">
                        </div>
                        <div class="orgmate-field"><label>Jumlah (Rp)</label>
                            <input name="amount" type="number" min="1" step="any" required>
                        </div>
                        <div class="orgmate-field"><label>Tanggal</label>
                            <input name="date" type="date" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                        <div class="orgmate-field"><label>Bukti kuitansi <span>(wajib — foto via kamera atau upload)</span></label>
                            <div class="proof-camera-row">
                                <input id="real-proof-{{ $budget->id }}" name="proof" type="file" accept="image/*,.pdf" capture="environment" required>
                                <button type="button" class="orgmate-btn ghost orgmate-btn-sm" data-camera-target="real-proof-{{ $budget->id }}">📷 Kamera</button>
                            </div>
                        </div>
                    </div>
                    <p class="orgmate-hint">Nominal langsung tercatat sebagai pengeluaran di Kas dan menambah realisasi anggaran.</p>
                    <div class="orgmate-dialog-actions">
                        <button type="button" class="orgmate-btn ghost" data-edit-close>Batal</button>
                        <button type="submit" class="orgmate-btn">Simpan Realisasi</button>
                    </div>
                </form>
            </dialog>
        @endforeach
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js" defer></script>
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
            document.addEventListener('DOMContentLoaded', function () {
                const canvas = document.getElementById('chartRealisasi');
                if (!canvas || !window.Chart) return;
                const labels = @json($budgets->pluck('program_name'));
                const allocated = @json($budgets->pluck('allocated')->map(fn ($v) => (float) $v)->all());
                const realized = @json($budgets->pluck('realized')->map(fn ($v) => (float) $v)->all());
                if (labels.length === 0) return;
                new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [
                            { label: 'Anggaran', data: allocated, backgroundColor: '#2563EB', borderRadius: 6 },
                            { label: 'Realisasi', data: realized, backgroundColor: '#22c55e', borderRadius: 6 },
                        ],
                    },
                    options: { responsive: true, plugins: { legend: { position: 'bottom' } } },
                });
            });
        </script>
        @include('partials.camera-capture')
    @endif
@endsection
