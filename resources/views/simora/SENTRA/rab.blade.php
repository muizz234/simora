@extends('simora.SENTRA.layout', ['title' => 'Anggaran & RAB'])

@section('content')
    <div class="sentra-head">
        <div>
            <h1>Anggaran per Proker (RAB)</h1>
            <p>Rencana anggaran biaya (RAB) setiap program kerja periode {{ $year }}.</p>
        </div>
        <div class="sentra-head-right">
            <form method="GET" action="{{ route('simora.sentra.rab') }}" class="sentra-periode">
                <i data-lucide="calendar-days" class="icon"></i><label>Periode:</label>
                <select name="periode" onchange="this.form.submit()">
                    @for($y = max(2025, (int) now()->format('Y') - 1); $y <= (int) now()->format('Y') + 2; $y++)
                        <option value="{{ $y }}" {{ $year === $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                @if($selectedOrganization)<input type="hidden" name="organization" value="{{ $selectedOrganization->slug }}">@endif
            </form>
            @if(($isDeveloper ?? false) && ($organizationsList ?? collect())->count() > 0)
                <form method="GET" action="{{ route('simora.sentra.rab') }}" class="sentra-periode">
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
        </div>
    </div>

    @if (session('status'))
        <div class="orgmate-toast is-show" style="position:static;opacity:1;transform:none;margin-bottom:12px">{{ session('status') }}</div>
    @endif

    @php
        $averagePercent = $totalAnggaran > 0 ? round(($totalRealisasi / $totalAnggaran) * 100) : 0;
    @endphp

    <div class="sentra-summary-grid">
        <article class="sentra-stat sentra-stat--blue"><span class="sentra-stat-icon"><i data-lucide="clipboard-list" class="icon"></i></span>
            <div><span>Total Anggaran</span><strong>Rp{{ number_format($totalAnggaran, 0, ',', '.') }}</strong><small>Periode {{ $year }}</small></div>
        </article>
        <article class="sentra-stat sentra-stat--green"><span class="sentra-stat-icon"><i data-lucide="receipt-text" class="icon"></i></span>
            <div><span>Total Realisasi</span><strong>Rp{{ number_format($totalRealisasi, 0, ',', '.') }}</strong><small>Anggaran yang sudah dipakai</small></div>
        </article>
        <article class="sentra-stat sentra-stat--purple"><span class="sentra-stat-icon"><i data-lucide="gauge" class="icon"></i></span>
            <div><span>Rata-rata Realisasi</span><strong>{{ $averagePercent }}%</strong><small>Realisasi ÷ Anggaran</small></div>
        </article>
        <article class="sentra-stat sentra-stat--red"><span class="sentra-stat-icon"><i data-lucide="wallet" class="icon"></i></span>
            <div><span>Sisa Anggaran</span><strong>Rp{{ number_format($totalAnggaran - $totalRealisasi, 0, ',', '.') }}</strong><small>Anggaran − Realisasi</small></div>
        </article>
    </div>

    <div class="orgmate-card">
        <div class="orgmate-card-head">
            <h2><i data-lucide="clipboard-list" class="icon"></i> Daftar RAB Program Kerja</h2>
            <span class="badge gray">Total Rp{{ number_format($totalAnggaran, 0, ',', '.') }}</span>
        </div>
        @if ($budgets->isEmpty())
            <div class="sentra-empty">Belum ada anggaran program kerja untuk periode {{ $year }}.<br>Tambahkan anggaran pertama melalui form di bawah.</div>
        @else
            <div class="orgmate-table-wrap">
                <table class="orgmate-table">
                    <thead>
                        <tr>
                            <th>Program Kerja</th>
                            <th style="text-align:right">Anggaran</th>
                            <th style="text-align:right">Realisasi</th>
                            <th>Persentase</th>
                            <th>Status</th>
                            @if(auth()->user()->role !== 'developer')<th>Aksi</th>@endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($budgets as $budget)
                            <tr>
                                <td><strong class="sentra-strong">{{ $budget->program_name }}</strong><small class="orgmate-field-hint">{{ $budget->workProgram?->name ?? (auth()->user()->role === 'developer' ? ($budget->organization?->name ?? '') : '') }}</small></td>
                                <td class="sentra-table-num">Rp{{ number_format($budget->allocated, 0, ',', '.') }}</td>
                                <td class="sentra-table-num">Rp{{ number_format($budget->realized, 0, ',', '.') }}</td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:8px;min-width:150px">
                                        <div class="orgmate-progress" style="flex:1;margin:0"><i style="width:{{ min($budget->percentage, 100) }}%"></i></div>
                                        <strong class="sentra-strong" style="font-size:12px">{{ rtrim(rtrim(number_format($budget->percentage, 2), '0'), '.') }}%</strong>
                                    </div>
                                </td>
                                <td>
                                    @if ($budget->percentage >= 100)
                                        <span class="badge red">Habis</span>
                                    @elseif ($budget->percentage >= 80)
                                        <span class="badge amber">Hampir Habis</span>
                                    @elseif ($budget->percentage > 0)
                                        <span class="badge blue">Berjalan</span>
                                    @else
                                        <span class="badge gray">Belum Dipakai</span>
                                    @endif
                                </td>
                                @if(auth()->user()->role !== 'developer')
                                    <td>
                                        <span class="orgmate-position-actions">
                                            <button type="button" class="orgmate-btn ghost orgmate-btn-sm" data-edit-open data-dialog="editRab-{{ $budget->id }}">Edit</button>
                                            <form method="POST" action="{{ route('simora.sentra.rab.destroy', $budget) }}" onsubmit="return confirm('Hapus anggaran {{ $budget->program_name }}?')">
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
            <div class="admin-pagination">{{ $budgets->links() }}</div>
        @endif
        <p class="orgmate-hint">Anggaran RAB digunakan sebagai acuan monitoring realisasi anggaran.</p>
    </div>

    @if(auth()->user()->role !== 'developer')
        <div class="orgmate-card">
            <div class="orgmate-card-head">
                <h2><i data-lucide="circle-plus" class="icon"></i> Tambah Anggaran Proker</h2>
            </div>
            <form method="POST" action="{{ route('simora.sentra.rab.store') }}" class="orgmate-form">
                @csrf
                <div class="orgmate-form-grid">
                    <div class="orgmate-field orgmate-field--wide"><label for="rabProgramName">Nama Program Kerja</label>
                        <input id="rabProgramName" name="program_name" value="{{ old('program_name') }}" placeholder="Contoh: Seminar Nasional" required>
                        @error('program_name')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field"><label for="rabWorkProgram">Teman proker E-Progress <span>(opsional)</span></label>
                        <select id="rabWorkProgram" name="work_program_id">
                            <option value="">— Manual / tanpa proker —</option>
                            @foreach ($workPrograms as $program)
                                <option value="{{ $program->id }}" @selected(old('work_program_id') == $program->id)>{{ $program->name }}</option>
                            @endforeach
                        </select>
                        @error('work_program_id')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field"><label for="rabYear">Tahun Anggaran</label>
                        <input id="rabYear" name="year" type="number" min="2000" max="2035" value="{{ old('year', $year) }}" required>
                        @error('year')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field"><label for="rabAllocated">Total Anggaran (Rp)</label>
                        <input id="rabAllocated" name="allocated" type="number" min="0" step="any" value="{{ old('allocated') }}" placeholder="5000000" required>
                        @error('allocated')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="orgmate-field"><label for="rabRealized">Realisasi Awal (Rp)</label>
                        <input id="rabRealized" name="realized" type="number" min="0" step="any" value="{{ old('realized', 0) }}" required>
                        @error('realized')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                    </div>
                </div>
                <button type="submit" class="orgmate-btn"><i data-lucide="save" class="icon"></i> Simpan RAB</button>
            </form>
        </div>

        @foreach ($budgets as $budget)
            <dialog class="orgmate-dialog" id="editRab-{{ $budget->id }}" aria-labelledby="editRabTitle-{{ $budget->id }}">
                <form method="dialog" class="orgmate-dialog-close-form" value="close">
                    <button type="submit" class="orgmate-icon-btn orgmate-dialog-close" aria-label="Tutup"><i data-lucide="x" class="icon" aria-hidden="true"></i></button>
                </form>
                <h2 class="orgmate-dialog-title" id="editRabTitle-{{ $budget->id }}">Edit Anggaran</h2>
                <form method="POST" action="{{ route('simora.sentra.rab.update', $budget) }}">
                    @csrf @method('PUT')
                    <div class="orgmate-form-grid">
                        <div class="orgmate-field orgmate-field--wide"><label>Nama Program Kerja</label>
                            <input name="program_name" value="{{ old('program_name', $budget->program_name) }}" required>
                            @error('program_name')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label>Teman proker E-Progress <span>(opsional)</span></label>
                            <select name="work_program_id">
                                <option value="">— Manual / tanpa proker —</option>
                                @foreach ($workPrograms as $program)
                                    <option value="{{ $program->id }}" @selected(old('work_program_id', $budget->work_program_id) == $program->id)>{{ $program->name }}</option>
                                @endforeach
                            </select>
                            @error('work_program_id')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label>Tahun Anggaran</label>
                            <input name="year" type="number" min="2000" max="2035" value="{{ old('year', $budget->year) }}" required>
                            @error('year')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label>Total Anggaran (Rp)</label>
                            <input name="allocated" type="number" min="0" step="any" value="{{ old('allocated', $budget->allocated) }}" required>
                            @error('allocated')<small class="orgmate-field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="orgmate-field"><label>Realisasi (Rp)</label>
                            <input name="realized" type="number" min="0" step="any" value="{{ old('realized', $budget->realized) }}" required>
                            @error('realized')<small class="orgmate-field-error">{{ $message }}</small>@enderror
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
        <div class="orgmate-empty">Developer mengelola anggaran lintas organisasi (mode view-only). Penyusunan RAB dilakukan oleh organisasi.</div>
    @endif
@endsection
