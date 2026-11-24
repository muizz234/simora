<?php

namespace App\Http\Controllers\Simora;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\CashTransaction;
use App\Models\Dues;
use App\Models\Organization;
use App\Modules\EProgress\Contracts\LpjDirectory;
use App\Modules\EProgress\Contracts\WorkProgramDirectory;
use App\Modules\EProgress\Data\LpjSummary;
use App\Modules\Orgmate\Contracts\MemberDirectory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SentraController extends Controller
{
    public function __construct(
        private readonly MemberDirectory $members,
        private readonly WorkProgramDirectory $workPrograms,
        private readonly LpjDirectory $lpjs,
    ) {}

    /**
     * Data keuangan masih kosong (belum ada tabel kas/anggaran/pengajuan/iuran).
     * Dashboard menampilkan nol dan keadaan kosong secara jujur.
     *
     * @return array<string, mixed>
     */
    private function emptyData(): array
    {
        $bulan = [];
        $pemasukan = [];
        $pengeluaran = [];

        for ($i = 1; $i <= 12; $i++) {
            $bulan[] = Carbon::create((int) now()->format('Y'), $i, 1)->locale('id')->translatedFormat('M');
            $pemasukan[] = 0;
            $pengeluaran[] = 0;
        }

        $anggaran = collect([]);
        $transaksi = collect([]);
        $pengajuan = collect([]);
        $iuran = collect([]);

        return compact('bulan', 'pemasukan', 'pengeluaran', 'anggaran', 'transaksi', 'pengajuan', 'iuran');
    }

    /**
     * Konteks organisasi seperti E-PROGRESS: role organization terikat ke
     * organisasi login; developer mengelola semua organisasi (pilih via ?organization=).
     *
     * @return array{isDeveloper: bool, organizationsList: Collection, selectedOrganization: ?Organization, orgName: string}
     */
    private function organizationContext(Request $request): array
    {
        $isDeveloper = $request->user()?->role === 'developer';
        $organizationsList = $isDeveloper
            ? Organization::query()->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'slug'])
            : collect([]);
        $selectedOrganization = $isDeveloper
            ? $organizationsList->firstWhere('slug', $request->query('organization'))
            : null;

        $orgName = $selectedOrganization?->name
            ?? ($isDeveloper ? 'Semua Organisasi' : $request->user()?->organization?->name ?? $organizationsList->first()?->name ?? 'Semua Organisasi');

        return compact('isDeveloper', 'organizationsList', 'selectedOrganization', 'orgName');
    }

    public function dashboard(Request $request): View
    {
        $context = $this->organizationContext($request);
        extract($context);

        $period = $request->query('periode', now()->format('Y'));
        $todayLabel = now()->locale('id')->translatedFormat('d F Y, H:i').' WIB';

        $cashQuery = CashTransaction::query();
        if ($isDeveloper) {
            $cashQuery->when($selectedOrganization !== null, fn ($q) => $q->where('organization_id', $selectedOrganization->id));
        } else {
            $cashQuery->where('organization_id', $request->user()->organization_id);
        }

        $totalPemasukan = (clone $cashQuery)->where('type', CashTransaction::TYPE_INCOME)->sum('amount');
        $totalPengeluaran = (clone $cashQuery)->where('type', CashTransaction::TYPE_EXPENSE)->sum('amount');

        $year = (int) $period;
        $budgetQuery = Budget::query()->when($isDeveloper && $selectedOrganization !== null, fn ($q) => $q->where('organization_id', $selectedOrganization->id));
        if (! $isDeveloper) {
            $budgetQuery->where('organization_id', $request->user()->organization_id);
        }
        $totalAnggaran = (clone $budgetQuery)->where('year', $year)->sum('allocated');
        $bulan = [];
        $pemasukan = [];
        $pengeluaran = [];
        for ($i = 1; $i <= 12; $i++) {
            $bulan[] = Carbon::create($year, $i, 1)->locale('id')->translatedFormat('M');
            $pemasukan[] = (float) (clone $cashQuery)
                ->where('type', CashTransaction::TYPE_INCOME)
                ->whereYear('date', $year)
                ->whereMonth('date', $i)
                ->sum('amount');
            $pengeluaran[] = (float) (clone $cashQuery)
                ->where('type', CashTransaction::TYPE_EXPENSE)
                ->whereYear('date', $year)
                ->whereMonth('date', $i)
                ->sum('amount');
        }

        $summary = [
            'totalKas' => $totalPemasukan - $totalPengeluaran,
            'totalPemasukan' => $totalPemasukan,
            'totalPengeluaran' => $totalPengeluaran,
            'totalAnggaran' => $totalAnggaran,
            'iuranLunas' => 0,
            'iuranTotal' => 0,
        ];

        return view('simora.SENTRA.dashboard', array_merge($context, compact('bulan', 'pemasukan', 'pengeluaran', 'period', 'todayLabel', 'summary')));
    }

    public function panduan(Request $request): View
    {
        $context = $this->organizationContext($request);
        $guides = $this->guideData();

        return view('simora.SENTRA.panduan', array_merge($context, compact('guides')));
    }

    public function kas(Request $request): View
    {
        $context = $this->organizationContext($request);

        $query = CashTransaction::query();

        if ($context['isDeveloper']) {
            $query->when($context['selectedOrganization'] !== null, fn ($q) => $q->where('organization_id', $context['selectedOrganization']->id));
        } else {
            $query->where('organization_id', $request->user()->organization_id);
        }

        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $query->where(fn ($sub) => $sub->where('title', 'like', '%'.$q.'%')->orWhere('category', 'like', '%'.$q.'%'));
        }

        $filters = request()->only('q');

        $isIncome = fn ($sub) => $sub->where('type', CashTransaction::TYPE_INCOME);
        $isExpense = fn ($sub) => $sub->where('type', CashTransaction::TYPE_EXPENSE);

        $totalPemasukan = (clone $query)->where($isIncome)->sum('amount');
        $totalPengeluaran = (clone $query)->where($isExpense)->sum('amount');

        $transaksi = $query->orderByDesc('date')->orderByDesc('id')->paginate(15)->withQueryString();

        return view('simora.SENTRA.kas', array_merge($context, compact('transaksi', 'totalPemasukan', 'totalPengeluaran', 'q', 'filters')));
    }

    public function rab(Request $request): View
    {
        $context = $this->organizationContext($request);
        $year = (int) $request->query('periode', now()->format('Y'));
        $year = min(max($year, 2000), 2035);

        $query = Budget::query();

        if ($context['isDeveloper']) {
            $query->when($context['selectedOrganization'] !== null, fn ($q) => $q->where('organization_id', $context['selectedOrganization']->id));
        } else {
            $query->where('organization_id', $request->user()->organization_id);
        }

        $totalAnggaran = (clone $query)->where('year', $year)->sum('allocated');
        $totalRealisasi = (clone $query)->where('year', $year)->sum('realized');

        $budgets = $query->where('year', $year)->orderBy('program_name')->paginate(15)->withQueryString();

        $organizationId = $request->user()->organization_id;
        $workPrograms = $request->user()->role === 'organization' && $organizationId !== null
            ? $this->workPrograms->forOrganization((int) $organizationId)
            : collect();

        return view('simora.SENTRA.rab', array_merge($context, compact('budgets', 'workPrograms', 'year', 'totalAnggaran', 'totalRealisasi')));
    }

    /**
     * Panduan penggunaan menu SENTRA.
     *
     * @return array<int, array{icon: string, title: string, desc: string, steps: array<int, string>}>
     */
    private function guideData(): array
    {
        return [
            ['icon' => 'wallet', 'title' => 'Kas Organisasi', 'desc' => 'Catat semua uang masuk dan keluar.', 'steps' => ['Tambah Transaksi: keterangan, jenis (Pemasukan/Pengeluaran), tanggal, nominal, kategori.', 'BUKTI WAJIB: lampirkan foto struk/kuitansi — klik 📷 Kamera untuk foto langsung atau upload file.', 'Saldo kas terhitung otomatis (pemasukan − pengeluaran) dan tampil di statistic dashboard.']],
            ['icon' => 'clipboard-list', 'title' => 'Anggaran & RAB', 'desc' => 'Rencanakan anggaran tiap proker.', 'steps' => ['Tambah Anggaran: nama proker, tautkan ke proker E-Progress (opsional), tahun, total anggaran, realisasi awal.', 'Bar persentase menunjukkan seberapa besar anggaran sudah dipakai.', 'Total anggaran tampil di dashboard dan menjadi acuan Realisasi.']],
            ['icon' => 'users', 'title' => 'Iuran Anggota', 'desc' => 'Tagih dan catat iuran anggota.', 'steps' => ['Catat iuran: pilih pengurus dari dropdown, nominal, periode, dan BUKTI pembayaran (wajib).', 'Pilih status Belum Bayar atau Lunas; bila Lunas, pemasukan otomatis masuk Kas.', 'Gunakan Tandai Lunas untuk menandai pelunasan; bukti tersalin ke kas-nya.']],
            ['icon' => 'receipt-text', 'title' => 'Realisasi Anggaran', 'desc' => 'Bandingkan RAB dengan pengeluaran nyata.', 'steps' => ['Lihat grafik dan persen realisasi tiap proker.', 'Klik Catat pada proker: jumlah pengeluaran + bukti kuitansi (wajib) → kas berkurang dan realisasi RAB naik.', 'Dokumen LPJ dari E-Progress tampil otomatis per proker sebagai bukti pertanggungjawaban.']],
            ['icon' => 'file-text', 'title' => 'Laporan Keuangan', 'desc' => 'Unduh laporan keuangan periodik.', 'steps' => ['Pilih periode tahun pada halaman Laporan.', 'Klik Unduh Laporan untuk format Excel/CSV: pemasukan, pengeluaran, dan saldo per bulan.', 'Lampirkan laporan saat rapat pertanggungjawaban LPJ.']],
        ];
    }

    public function iuran(Request $request): View
    {
        $context = $this->organizationContext($request);

        $query = Dues::query();

        if ($context['isDeveloper']) {
            $query->when($context['selectedOrganization'] !== null, fn ($q) => $q->where('organization_id', $context['selectedOrganization']->id));
        } else {
            $query->where('organization_id', $request->user()->organization_id);
        }

        $period = trim((string) $request->query('periode', ''));

        if ($period !== '') {
            $query->where(fn ($sub) => $sub->where('period', 'like', '%'.$period.'%'));
        }

        $status = $request->query('status');
        if (in_array($status, [Dues::STATUS_PAID, Dues::STATUS_UNPAID], true)) {
            $query->where('status', $status);
        }

        $total = (clone $query)->count();
        $paid = (clone $query)->where('status', Dues::STATUS_PAID)->count();
        $collected = (float) (clone $query)->where('status', Dues::STATUS_PAID)->sum('amount');
        $percent = $total > 0 ? round(($paid / $total) * 100) : 0;

        $dues = $query->orderBy('name')->paginate(15)->withQueryString();

        $organizationId = $request->user()->organization_id;
        $members = $request->user()->role === 'organization' && $organizationId !== null
            ? $this->members->forOrganization((int) $organizationId)
            : collect();

        return view('simora.SENTRA.iuran', array_merge($context, compact('dues', 'members', 'period', 'status', 'total', 'paid', 'collected', 'percent')));
    }

    public function realisasi(Request $request): View
    {
        $context = $this->organizationContext($request);
        $year = (int) $request->query('periode', now()->format('Y'));
        $year = min(max($year, 2000), 2035);

        $budgetQuery = Budget::query()->with('organization');

        if ($context['isDeveloper']) {
            $budgetQuery->when($context['selectedOrganization'] !== null, fn ($q) => $q->where('organization_id', $context['selectedOrganization']->id));
        } else {
            $budgetQuery->where('organization_id', $request->user()->organization_id);
        }

        $budgets = $budgetQuery->where('year', $year)->orderBy('program_name')->get();

        // LPJ dari E-PROGRESS diakses lewat batas baca modul (bukan query tabel langsung).
        $lpjOrganizationId = $context['isDeveloper']
            ? $context['selectedOrganization']?->id
            : $request->user()->organization_id;

        $lpjs = $this->lpjs->forOrganization($lpjOrganizationId !== null ? (int) $lpjOrganizationId : null);
        $lpjsByProgram = $lpjs->filter(fn (LpjSummary $lpj): bool => $lpj->workProgramId !== null)
            ->groupBy(fn (LpjSummary $lpj): int => (int) $lpj->workProgramId);

        $totalAnggaran = (float) $budgets->sum('allocated');
        $totalRealisasi = (float) $budgets->sum('realized');
        $averagePercent = $totalAnggaran > 0 ? round(($totalRealisasi / $totalAnggaran) * 100) : 0;

        return view('simora.SENTRA.realisasi', array_merge($context, compact(
            'budgets',
            'lpjs',
            'lpjsByProgram',
            'year',
            'totalAnggaran',
            'totalRealisasi',
            'averagePercent',
        )));
    }

    public function laporan(Request $request)
    {
        $d = $this->emptyData();
        $context = $this->organizationContext($request);
        extract($context);

        $periode = $request->query('periode', now()->format('Y'));

        if ($request->query('format') === 'excel') {
            $rows = [
                ['Laporan Keuangan Periodik SENTRA — Periode '.$periode],
                ['Organisasi', $orgName],
                [],
                ['Bulan', 'Pemasukan (Rp)', 'Pengeluaran (Rp)', 'Saldo (Rp)'],
            ];
            foreach ($d['bulan'] as $i => $b) {
                $rows[] = [$b, 0, 0, 0];
            }
            $rows[] = [];
            $rows[] = ['Proker', 'Anggaran (Rp)', 'Realisasi (Rp)', 'Persentase (%)'];
            $rows[] = ['Belum ada data anggaran', 0, 0, 0];

            $filename = 'laporan-keuangan-'.$periode.'.csv';
            $callback = function () use ($rows) {
                $out = fopen('php://output', 'w');
                foreach ($rows as $row) {
                    fputcsv($out, $row);
                }
                fclose($out);
            };

            return response()->streamDownload($callback, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        return view('simora.SENTRA.laporan', array_merge($d, $context, compact('periode')));
    }
}
