<?php

namespace App\Http\Controllers\Simora;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Attendance;
use App\Models\Committee;
use App\Models\Documentation;
use App\Models\Evaluation;
use App\Models\Member;
use App\Models\Organization;
use App\Models\WorkProgram;
use App\Support\OrganizationScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class EProgressController extends Controller
{
    public function dashboard(Request $request): View
    {
        $organizationsList = $this->scopedOrganizations($request)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'full_name', 'slug', 'period', 'focus']);

        // Role organization selalu terikat ke organisasi akun login.
        // Role developer melihat semua organisasi (pilih lewat ?organization=, kosong = semua).
        $isDeveloper = $request->user()?->role === 'developer';
        if ($isDeveloper) {
            $selectedSlug = $request->query('organization');
            $selectedOrganization = $organizationsList->firstWhere('slug', $selectedSlug);
        } else {
            $selectedOrganization = $request->user()?->organization()->first() ?? $organizationsList->first();
        }

        $periodLabel = $selectedOrganization?->period ?? ($isDeveloper ? 'Semua periode' : 'Periode belum diatur');

        // Statistik nyata dari database modul (scope organisasi seperti E-PROGRESS).
        $scope = fn ($query) => $selectedOrganization !== null
            ? $query->where('organization_id', $selectedOrganization->id)
            : ($isDeveloper ? $query : $query->where('organization_id', $request->user()->organization_id));

        $stats = [
            'totalKegiatan' => (clone $scope(Activity::query()))->count(),
            'totalProker' => (clone $scope(WorkProgram::query()))->count(),
            'pesertaTerlibat' => (clone $scope(Attendance::query()))->count(),
            'kegiatanSelesai' => (clone $scope(Activity::query()))->where('status', 'selesai')->count(),
        ];

        $recentActivities = $scope(Activity::query())
            ->orderByDesc('start_at')
            ->limit(5)
            ->get();

        // 6 bulan terakhir untuk grafik (berdasarkan tanggal mulai kegiatan)
        $bulan = [];
        $grafikTotal = [];
        $grafikSelesai = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->startOfMonth()->subMonths($i);
            $bulan[] = $month->locale('id')->translatedFormat('M');
            $grafikTotal[] = (clone $scope(Activity::query()))
                ->whereYear('start_at', $month->format('Y'))
                ->whereMonth('start_at', $month->format('m'))
                ->count();
            $grafikSelesai[] = (clone $scope(Activity::query()))
                ->whereYear('start_at', $month->format('Y'))
                ->whereMonth('start_at', $month->format('m'))
                ->where('status', 'selesai')
                ->count();
        }

        $todayLabel = now()->locale('id')->translatedFormat('l, d F Y');
        $timeLabel = now()->format('H:i').' WIB';
        $orgName = $selectedOrganization?->name ?? ($isDeveloper ? 'Semua Organisasi' : $request->user()->organization?->name ?? $organizationsList->first()?->name);

        return view('simora.E-PROGRESS.dashboard', compact(
            'organizationsList',
            'selectedOrganization',
            'isDeveloper',
            'periodLabel',
            'stats',
            'bulan',
            'grafikTotal',
            'grafikSelesai',
            'todayLabel',
            'timeLabel',
            'orgName',
            'recentActivities',
        ));
    }

    public function panduan(Request $request): View
    {
        $context = $this->organizationContext($request);
        $guides = $this->guideData();

        return view('simora.E-PROGRESS.panduan', array_merge($context, compact('guides')));
    }

    public function monitoring(Request $request): View
    {
        $context = $this->organizationContext($request);

        $query = WorkProgram::query()->with(['organization', 'activities' => fn ($q) => $q->orderByDesc('start_at')]);

        if ($context['isDeveloper']) {
            $query->when($context['selectedOrganization'] !== null, fn ($q) => $q->where('organization_id', $context['selectedOrganization']->id));
        } else {
            $query->where('organization_id', $request->user()->organization_id);
        }

        $programs = $query->orderBy('name')->get();

        $summaries = $programs->map(function (WorkProgram $program) {
            $activities = $program->activities;
            $total = $activities->count();
            $done = $activities->where('status', 'selesai')->count();
            $byStage = $activities->groupBy('status')->map->count();

            return [
                'program' => $program,
                'totalKegiatan' => $total,
                'selesai' => $done,
                'progress' => $total > 0 ? round(($done / $total) * 100) : 0,
                'byStage' => $byStage,
                'lastActivity' => $activities->firstWhere(fn ($activity) => $activity->start_at !== null),
            ];
        });

        $average = $summaries->count() > 0 ? round($summaries->avg('progress')) : 0;

        $stats = [
            'totalProker' => $programs->count(),
            'rataRata' => $average,
            'selesai' => $summaries->where('progress', 100)->count(),
            'belumMulai' => $summaries->where('totalKegiatan', 0)->count(),
        ];

        return view('simora.E-PROGRESS.monitoring', array_merge($context, compact('summaries', 'stats')));
    }

    public function evaluasi(Request $request): View
    {
        $context = $this->organizationContext($request);

        $activityQuery = Activity::query()->with('workProgram');

        if ($context['isDeveloper']) {
            $activityQuery->when($context['selectedOrganization'] !== null, fn ($q) => $q->where('organization_id', $context['selectedOrganization']->id));
        } else {
            $activityQuery->where('organization_id', $request->user()->organization_id);
        }

        $activities = $activityQuery->orderByDesc('start_at')->get();

        $selectedActivity = $activities->firstWhere('id', $request->query('activity')) ?? $activities->first();

        $typeFilter = $request->query('type');
        $typeFilter = in_array($typeFilter, [Evaluation::TYPE_EVALUATION, Evaluation::TYPE_REPORT], true)
            ? $typeFilter
            : null;

        $evaluationQuery = $selectedActivity
            ? Evaluation::where('activity_id', $selectedActivity->id)
            : Evaluation::query()->whereRaw('1 = 0');

        if ($typeFilter !== null) {
            $evaluationQuery->where('type', $typeFilter);
        }

        $evaluations = $evaluationQuery->orderByDesc('created_at')->get();

        $stats = $selectedActivity
            ? [
                'total' => Evaluation::where('activity_id', $selectedActivity->id)->count(),
                'evaluasi' => Evaluation::where('activity_id', $selectedActivity->id)->where('type', Evaluation::TYPE_EVALUATION)->count(),
                'lpj' => Evaluation::where('activity_id', $selectedActivity->id)->where('type', Evaluation::TYPE_REPORT)->count(),
            ]
            : ['total' => 0, 'evaluasi' => 0, 'lpj' => 0];

        return view('simora.E-PROGRESS.evaluasi', array_merge($context, compact(
            'activities',
            'selectedActivity',
            'evaluations',
            'typeFilter',
            'stats',
        )));
    }

    public function dokumentasi(Request $request): View
    {
        $context = $this->organizationContext($request);

        $activityQuery = Activity::query()->with('workProgram');

        if ($context['isDeveloper']) {
            $activityQuery->when($context['selectedOrganization'] !== null, fn ($q) => $q->where('organization_id', $context['selectedOrganization']->id));
        } else {
            $activityQuery->where('organization_id', $request->user()->organization_id);
        }

        $activities = $activityQuery->orderByDesc('start_at')->get();

        $selectedActivity = $activities->firstWhere('id', $request->query('activity')) ?? $activities->first();

        $documentations = $selectedActivity
            ? Documentation::where('activity_id', $selectedActivity->id)->orderByDesc('created_at')->get()
            : collect();

        $stats = [
            'totalDokumen' => $documentations->count(),
            'foto' => $documentations->where('type', 'foto')->count(),
            'video' => $documentations->where('type', 'video')->count(),
            'dokumen' => $documentations->where('type', 'dokumen')->count(),
        ];

        return view('simora.E-PROGRESS.dokumentasi', array_merge($context, compact(
            'activities',
            'selectedActivity',
            'documentations',
            'stats',
        )));
    }

    public function presensi(Request $request): View
    {
        $context = $this->organizationContext($request);

        $activityQuery = Activity::query()->with('workProgram');

        if ($context['isDeveloper']) {
            $activityQuery->when($context['selectedOrganization'] !== null, fn ($q) => $q->where('organization_id', $context['selectedOrganization']->id));
        } else {
            $activityQuery->where('organization_id', $request->user()->organization_id);
        }

        $activities = $activityQuery->orderByDesc('start_at')->get();

        $selectedActivity = $activities->firstWhere('id', $request->query('activity')) ?? $activities->first();

        $search = trim((string) $request->query('q', ''));

        $attendanceQuery = $selectedActivity
            ? Attendance::where('activity_id', $selectedActivity->id)
            : Attendance::query()->whereRaw('1 = 0');

        if ($search !== '') {
            $attendanceQuery->where('name', 'like', '%'.$search.'%');
        }

        $attendances = $attendanceQuery->orderByDesc('checked_at')->get();

        $checkinUrl = $selectedActivity
            ? URL::signedRoute('presensi.checkin', ['activity' => $selectedActivity->id])
            : null;

        $stats = [
            'totalHadir' => $attendances->count(),
            'viaQr' => $attendances->where('method', 'qr')->count(),
            'manual' => $attendances->where('method', 'manual')->count(),
        ];

        return view('simora.E-PROGRESS.presensi', array_merge($context, compact(
            'activities',
            'selectedActivity',
            'attendances',
            'checkinUrl',
            'stats',
            'search',
        )));
    }

    public function kepanitiaan(Request $request): View
    {
        $context = $this->organizationContext($request);

        $activityQuery = Activity::query()->with('workProgram');

        if ($context['isDeveloper']) {
            $activityQuery->when($context['selectedOrganization'] !== null, fn ($q) => $q->where('organization_id', $context['selectedOrganization']->id));
        } else {
            $activityQuery->where('organization_id', $request->user()->organization_id);
        }

        $activities = $activityQuery->orderByDesc('start_at')->get();

        $selectedActivityId = $request->query('activity');
        $selectedActivity = $activities->firstWhere('id', $selectedActivityId) ?? $activities->first();

        $committees = $selectedActivity
            ? Committee::where('activity_id', $selectedActivity->id)->orderBy('name')->get()
            : collect();

        $members = $request->user()->role === 'organization'
            ? Member::where('organization_id', $request->user()->organization_id)->orderBy('name')->get(['id', 'name'])
            : collect();

        $stats = [
            'totalKegiatan' => $activities->count(),
            'kegiatanBerpanitia' => Committee::query()
                ->when($context['isDeveloper'] && $context['selectedOrganization'] !== null, fn ($q) => $q->where('organization_id', $context['selectedOrganization']->id))
                ->when(! $context['isDeveloper'], fn ($q) => $q->where('organization_id', $request->user()->organization_id))
                ->distinct('activity_id')
                ->count('activity_id'),
            'totalPanitia' => Committee::query()
                ->when($context['isDeveloper'] && $context['selectedOrganization'] !== null, fn ($q) => $q->where('organization_id', $context['selectedOrganization']->id))
                ->when(! $context['isDeveloper'], fn ($q) => $q->where('organization_id', $request->user()->organization_id))
                ->count(),
        ];

        return view('simora.E-PROGRESS.kepanitiaan', array_merge($context, compact(
            'activities',
            'selectedActivity',
            'committees',
            'members',
            'stats',
        )));
    }

    public function kalender(Request $request): View
    {
        $context = $this->organizationContext($request);

        $year = (int) $request->query('year', now()->format('Y'));
        $month = (int) $request->query('month', now()->format('m'));
        $month = min(max($month, 1), 12);

        $query = Activity::query()->with('workProgram');

        if ($context['isDeveloper']) {
            $query->when($context['selectedOrganization'] !== null, fn ($q) => $q->where('organization_id', $context['selectedOrganization']->id));
        } else {
            $query->where('organization_id', $request->user()->organization_id);
        }

        $monthActivities = (clone $query)
            ->whereYear('start_at', $year)
            ->whereMonth('start_at', $month)
            ->orderBy('start_at')
            ->get();

        $activitiesByDay = $monthActivities->groupBy(fn ($activity) => $activity->start_at?->format('Y-m-d'));

        $days = [];
        $cursor = Carbon::create($year, $month, 1)->startOfMonth();
        $firstWeekday = (int) $cursor->format('N'); // 1=Senin .. 7=Minggu

        if ($firstWeekday > 1) {
            $previous = $cursor->copy()->subDays($firstWeekday - 1);

            for ($i = 0; $i < $firstWeekday - 1; $i++) {
                $days[] = ['date' => $previous->copy()->addDays($i), 'inMonth' => false];
            }
        }

        $cursor = Carbon::create($year, $month, 1)->startOfMonth();
        while ($cursor->month === $month) {
            $days[] = ['date' => $cursor->copy(), 'inMonth' => true];
            $cursor->addDay();
        }

        while (count($days) % 7 !== 0) {
            $days[] = ['date' => $cursor->copy(), 'inMonth' => false];
            $cursor->addDay();
        }

        $workPrograms = $request->user()->role === 'organization'
            ? WorkProgram::where('organization_id', $request->user()->organization_id)->orderBy('name')->get(['id', 'name'])
            : collect();

        $prev = Carbon::create($year, $month, 1)->subMonth();
        $next = Carbon::create($year, $month, 1)->addMonth();

        return view('simora.E-PROGRESS.kalender', array_merge($context, compact(
            'monthActivities',
            'activitiesByDay',
            'days',
            'workPrograms',
            'year',
            'month',
            'prev',
            'next',
        )));
    }

    /**
     * Panduan penggunaan menu E-Progress.
     *
     * @return array<int, array{icon: string, title: string, desc: string, steps: array<int, string>}>
     */
    private function guideData(): array
    {
        return [
            ['icon' => 'layers', 'title' => 'Proker', 'desc' => 'Rencanakan program kerja organisasi.', 'steps' => ['Buka menu Proker → Tambah Program Kerja: nama, deskripsi, status tahapan, tanggal.', 'Setiap proker menjadi acuan anggaran RAB di SENTRA dan laporan di halaman publik.', 'Perbarui status tahapan (perencanaan–selesai) saat proker berjalan.']],
            ['icon' => 'calendar-days', 'title' => 'Kalender Kegiatan', 'desc' => 'Jadwalkan kegiatan bulanan.', 'steps' => ['Buka Kalender Kegiatan → navigasi bulan dengan Sebelumnya/Berikutnya.', 'Tambah kegiatan: nama, proker terkait, lokasi, waktu mulai/selesai, status.', 'Kegiatan muncul sebagai chip di kalender dan daftar bulan ini.']],
            ['icon' => 'users', 'title' => 'Kepanitiaan', 'desc' => 'Susun panitia tiap kegiatan.', 'steps' => ['Pilih kegiatan pada dropdown.', 'Tambah panitia: nama (saran otomatis dari Data Anggota) dan peran (Ketua Pelaksana, Sekretaris, dll.).', 'Edit/hapus panitia yang sudah tidak bertugas.']],
            ['icon' => 'qr-code', 'title' => 'Absensi / Presensi (QR Code)', 'desc' => 'Presensi peserta via scan QR.', 'steps' => ['Pilih kegiatan → QR presensi tampil besar.', 'Tayangkan/cetak QR di pintu masuk (tombol Unduh QR / Cetak).', 'Peserta scan QR → halaman check-in terbuka → isi nama → Saya Hadir.', 'Panitia bisa mencatat manual bila peserta terkendala scan.']],
            ['icon' => 'image', 'title' => 'Dokumentasi Kegiatan', 'desc' => 'Simpan foto, video, dokumen kegiatan.', 'steps' => ['Pilih kegiatan → Unggah Dokumentasi (boleh banyak berkas sekaligus, maks 10).', 'Foto tampil galeri grid; klik untuk membuka ukuran penuh.', 'Dokumentasi terbaik tampil di halaman publik Kegiatan beserta nama kegiatan, logo, dan organisasi.']],
            ['icon' => 'file-check', 'title' => 'Evaluasi & LPJ', 'desc' => 'Tulis evaluasi dan laporan pertanggungjawaban.', 'steps' => ['Pilih kegiatan → Tulis Dokumen: judul, tipe Evaluasi atau LPJ, status Draft/Selesai.', 'Gunakan filter tab Semua/Evaluasi/LPJ untuk memilah dokumen.', 'LPJ yang selesai tertaut otomatis di SENTRA → Realisasi Anggaran sebagai bukti.']],
            ['icon' => 'bar-chart-3', 'title' => 'Monitoring Progres Proker', 'desc' => 'Pantau kemajuan proker secara nyata.', 'steps' => ['Progress dihitung dari kegiatan berstatus Selesai per proker (bukan angka manual).', 'Lihat bar progres, rincian tahapan, dan kegiatan terakhir tiap proker.', 'Developer melihat lintas organisasi lewat pemilih organisasi.']],
        ];
    }

    /**
     * Konteks organisasi seperti pola E-PROGRESS.
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

    private function scopedOrganizations(Request $request): Builder
    {
        return OrganizationScope::forUser(Organization::query(), $request->user(), 'id');
    }
}
