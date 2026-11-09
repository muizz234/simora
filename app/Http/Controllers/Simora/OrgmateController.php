<?php

namespace App\Http\Controllers\Simora;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Archive;
use App\Models\Mahasiswa;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Position;
use App\Models\User;
use App\Support\OrganizationScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrgmateController extends Controller
{
    public function panduan(Request $request): View
    {
        $guides = $this->guideData();

        return view('simora.ORGMATE.panduan', array_merge($this->organizationContext($request), compact('guides')));
    }

    public function dashboard(Request $request): View
    {
        $user = $request->user();

        $organizationsList = $this->scopedOrganizations($request)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'full_name', 'slug', 'period', 'focus', 'registration_url']);

        $isDeveloper = $user->role === 'developer';
        $selectedSlug = $request->query('organization');
        // Developer tanpa pilihan melihat agregat semua organisasi; role organization selalu terikat ke organisasi login.
        $selectedOrganization = $organizationsList->firstWhere('slug', $selectedSlug);

        $usersQuery = User::query();
        if ($user->role === 'organization') {
            $usersQuery->where('organization_id', $user->organization_id);
        } elseif ($selectedOrganization) {
            $usersQuery->where('organization_id', $selectedOrganization->id);
        }
        // Developer tanpa pilihan: tidak difilter (agregat semua organisasi).

        $totalAnggota = (clone $usersQuery)->count();
        // Pengurus didefinisikan sebagai akun dengan role organization dalam scope terpilih.
        $totalPengurus = (clone $usersQuery)->where('role', 'organization')->count();
        // Data divisi/dokumen/pengumuman belum memiliki tabel, tampilkan 0 secara jujur.
        $totalDivisi = 0;
        $totalDokumen = 0;
        $totalPengumuman = 0;

        $totalAnggotaAktif = (clone $usersQuery)->whereNotNull('email_verified_at')->count();
        $totalAnggotaNonaktif = max(0, $totalAnggota - $totalAnggotaAktif);

        $recentMembers = $organizationIdForMembers = $user->role === 'organization'
            ? Member::where('organization_id', $user->organization_id)
            : ($selectedOrganization ? Member::where('organization_id', $selectedOrganization->id) : Member::query());
        $recentMembers = $recentMembers->with('organization')
            ->latest('created_at')
            ->limit(5)
            ->get();

        $allMembers = (clone $usersQuery)->get(['id', 'created_at', 'role', 'email_verified_at']);
        $activeRoleCount = $allMembers->where('role', 'organization')->count();
        $memberRoleCount = $allMembers->where('role', 'user')->count();

        // Chart Jan–Des tahun berjalan (seperti contoh): anggota aktif (kumulatif terverifikasi) & pendaftaran baru.
        $bulan = [];
        $anggotaAktifSeries = [];
        $pendaftaranBaru = [];
        $running = 0;
        $runningActive = 0;
        $year = (int) now()->format('Y');
        for ($m = 1; $m <= 12; $m++) {
            $ym = sprintf('%04d-%02d', $year, $m);
            $count = $allMembers->filter(fn ($u) => $u->created_at && $u->created_at->format('Y-m') === $ym)->count();
            $activeCount = $allMembers->filter(fn ($u) => $u->created_at && $u->created_at->format('Y-m') === $ym && $u->email_verified_at)->count();
            $running += $count;
            $runningActive += $activeCount;
            $bulan[] = Carbon::create($year, $m, 1)->locale('id')->translatedFormat('M');
            $pendaftaranBaru[] = $count;
            $anggotaAktifSeries[] = $runningActive;
        }

        $organizationsCount = $organizationsList->count();
        $usersCount = $totalAnggota;
        $organizationUsersCount = $totalPengurus;
        $activePeriods = $organizationsList;

        $greeting = $this->greeting();
        $todayLabel = now()->locale('id')->translatedFormat('l, d F Y');

        $hijriLabel = null;
        if (class_exists(\IntlDateFormatter::class)) {
            try {
                $fmt = new \IntlDateFormatter('id-ID', \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, null, \IntlCalendar::ISLAMIC_UMALQURA);
                $fmt->setPattern('d MMMM yyyy');
                $hijriLabel = $fmt->format(now()->getTimestamp());
            } catch (\Throwable $e) {
                $hijriLabel = null;
            }
        }

        $orgName = $selectedOrganization?->name ?? ($isDeveloper ? 'Semua Organisasi' : $user->organization?->name ?? $organizationsList->first()?->name);

        return view('simora.ORGMATE.dashboard', compact(
            'organizationsList',
            'selectedOrganization',
            'isDeveloper',
            'totalAnggota',
            'totalPengurus',
            'totalDivisi',
            'totalDokumen',
            'totalPengumuman',
            'totalAnggotaAktif',
            'totalAnggotaNonaktif',
            'recentMembers',
            'bulan',
            'pendaftaranBaru',
            'anggotaAktifSeries',
            'hijriLabel',
            'orgName',
            'activeRoleCount',
            'memberRoleCount',
            'organizationsCount',
            'usersCount',
            'organizationUsersCount',
            'activePeriods',
            'greeting',
            'todayLabel',
        ));
    }

    public function struktur(Request $request): View
    {
        $context = $this->organizationContext($request);

        $query = Position::query()->with('member');

        // Developer: mode monitoring (semua organisasi atau pilihan ?organization=).
        // Role organization: hanya organisasinya sendiri (seperti E-PROGRESS).
        if ($context['isDeveloper']) {
            $query->when($context['selectedOrganization'] !== null, fn ($q) => $q->where('organization_id', $context['selectedOrganization']->id));
        } else {
            $query->where('organization_id', $request->user()->organization_id);
        }

        $positions = $query->orderBy('sort_order')->orderBy('name')->get();

        $members = $request->user()->organization_id
            ? Member::where('organization_id', $request->user()->organization_id)->orderBy('name')->get(['id', 'name'])
            : collect();

        // Dropdown pemegang jabatan: mahasiswa SSO SESUAI PRODI organisasi ini,
        // dikelompokkan per angkatan (tahun masuk) + dicari via nama/NIM di UI.
        // Pemegang tetap disimpan sebagai member (positions.member_id) lewat auto-create.
        $myOrganization = $request->user()->organization;
        $prodiLabel = $request->user()->role === 'organization' ? ($myOrganization?->focus ?? '') : '';

        [$mahasiswaByAngkatan, $angkatanOptions] = $this->mahasiswaCandidates($prodiLabel);

        return view('simora.ORGMATE.struktur', array_merge($context, compact('positions', 'members', 'mahasiswaByAngkatan', 'angkatanOptions', 'prodiLabel')));
    }

    public function anggota(Request $request): View
    {
        // Data anggota (mahasiswa) dari tabel members — bukan akun login.
        $query = Member::query()->with('organization');

        if ($request->user()->role === 'organization') {
            $query->where('organization_id', $request->user()->organization_id);
        }

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nim', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = min(max((int) $request->query('per_page', 10), 5), 50);
        $members = $query->orderBy('name')->paginate($perPage)->withQueryString();

        // Opsi dropdown divisi anggota: kelompok pengurus + divisi dari koordinator.
        $coordinatorDivisions = Position::where('name', 'like', 'Koordinator Divisi%')
            ->when($request->user()->organization_id, fn ($q) => $q->where('organization_id', $request->user()->organization_id))
            ->orderBy('sort_order')
            ->get(['name'])
            ->map(fn ($position) => preg_replace('/^Koordinator Divisi\s+/i', '', $position->name));
        $divisionOptions = collect(['Pimpinan', 'Wakil Pimpinan', 'BPI (Badan Pengurus Inti)'])
            ->merge($coordinatorDivisions)
            ->unique()
            ->values();

        // Dropdown tambah anggota: mahasiswa SESUAI PRODI organisasi ini,
        // dikelompokkan per angkatan + dicari via nama/NIM.
        $myOrganization = $request->user()->organization;
        $prodiLabel = $request->user()->role === 'organization' ? ($myOrganization?->focus ?? '') : '';

        [$mahasiswaByAngkatan, $angkatanOptions] = $this->mahasiswaCandidates($prodiLabel);

        return view('simora.ORGMATE.anggota', array_merge($this->organizationContext($request), compact('members', 'search', 'divisionOptions', 'mahasiswaByAngkatan', 'angkatanOptions', 'prodiLabel')));
    }

    public function rekrutmen(Request $request): View
    {
        $organizations = $this->scopedOrganizations($request)->orderBy('sort_order')->orderBy('name')->get(['name', 'full_name', 'logo_path', 'registration_url', 'period']);

        return view('simora.ORGMATE.rekrutmen', compact('organizations'));
    }

    public function arsip(Request $request): View
    {
        $context = $this->organizationContext($request);

        $query = Archive::query()->with('uploader');

        // Developer: mode monitoring (semua organisasi atau pilihan ?organization=).
        // Role organization: hanya organisasinya sendiri (seperti E-PROGRESS).
        if ($context['isDeveloper']) {
            $query->when($context['selectedOrganization'] !== null, fn ($q) => $q->where('organization_id', $context['selectedOrganization']->id));
        } else {
            $query->where('organization_id', $request->user()->organization_id);
        }

        $archives = $query->orderByDesc('date')->orderBy('title')->paginate(10)->withQueryString();

        $countsQuery = Archive::query();

        if ($context['isDeveloper']) {
            $countsQuery->when($context['selectedOrganization'] !== null, fn ($q) => $q->where('organization_id', $context['selectedOrganization']->id));
        } else {
            $countsQuery->where('organization_id', $request->user()->organization_id);
        }

        $counts = $countsQuery->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        return view('simora.ORGMATE.arsip', array_merge($context, compact('archives', 'counts')));
    }

    public function pengumuman(Request $request): View
    {
        $context = $this->organizationContext($request);

        $query = Announcement::query()->with('creator');

        // Developer: mode monitoring (semua organisasi atau pilihan ?organization=).
        // Role organization: hanya organisasinya sendiri (seperti E-PROGRESS).
        if ($context['isDeveloper']) {
            $query->when($context['selectedOrganization'] !== null, fn ($q) => $q->where('organization_id', $context['selectedOrganization']->id));
        } else {
            $query->where('organization_id', $request->user()->organization_id);
        }

        $announcements = $query->orderByDesc('published_at')->orderByDesc('created_at')->paginate(10)->withQueryString();

        return view('simora.ORGMATE.pengumuman', array_merge($context, compact('announcements')));
    }

    private function scopedOrganizations(Request $request): Builder
    {
        return OrganizationScope::forUser(Organization::query(), $request->user(), 'id');
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

    private function guideData(): array
    {
        return [
            ['icon' => 'network', 'title' => 'Struktur Kepengurusan', 'desc' => 'Atur jabatan dan siapa pemegangnya.', 'steps' => ['Buka menu Struktur Kepengurusan → Tambah Jabatan (mis. Ketua Umum, Koordinator Divisi).', 'Pilih kelompok jabatan: Pimpinan, Wakil Pimpinan, BPI, atau Divisi.', 'Tunjuk pemegang dari daftar mahasiswa SESUAI PRODI organisasi Anda, dikelompokkan per angkatan. Saring dengan kolom pencarian (nama/NIM) atau filter angkatan agar cepat, lalu pilih nama di dropdown.', 'Satu jabatan, satu pemegang. Perwakilan otomatis menjadi data anggota untuk bagan struktural di landing publik.']],
            ['icon' => 'users', 'title' => 'Data Anggota', 'desc' => 'Kelola data mahasiswa anggota organisasi.', 'steps' => ['Tambah anggota lewat form: pilih mahasiswa SESUAI PRODI organisasi Anda dari dropdown (dikelompokkan per angkatan). Saring dengan kolom pencarian (nama/NIM) atau filter angkatan. NIM terisi otomatis.', 'Pilih divisi dan unggah foto anggota bila ada.', 'Cari anggota lewat kotak pencarian dan ubah lewat tombol Edit.', 'Gunakan pagination (5–50 per halaman) untuk daftar panjang.']],
            ['icon' => 'folder-open', 'title' => 'Arsip SK & Surat', 'desc' => 'Simpan dokumen SK dan surat dalam satu tempat.', 'steps' => ['Tambah arsip: judul, kategori, dan file (PDF/gambar/dokumen).', 'File tersimpan aman dan bisa dibuka/diunduh kembali.', 'Hitungan kartu menampilkan jumlah arsip per kategori.']],
            ['icon' => 'megaphone', 'title' => 'Pengumuman', 'desc' => 'Sampaikan informasi penting ke anggota.', 'steps' => ['Tulis pengumuman: judul dan isi.', 'Pengumuman terbaru tampil di dashboard organisasi.', 'Edit atau hapus pengumuman lama bila sudah tidak relevan.']],
            ['icon' => 'user-plus', 'title' => 'Rekrutmen Anggota', 'desc' => 'Kelola open recruitment dan calon anggota.', 'steps' => ['Buka menu Rekrutmen Anggota untuk melihat calon.', 'Tautkan link pendaftaran organisasi di panel SIMORA agar tampil di landing.', 'Calon yang diterima bisa ditambahkan sebagai anggota lewat Data Anggota.']],
        ];
    }

    /**
     * Kandidat mahasiswa untuk dropdown organisasi per prodi + per angkatan.
     * Bergantung pada fokus organisasi: hanya prodi itu yang ditampilkan.
     *
     * @return array{0: Collection<string, Collection<int, Mahasiswa>>, 1: Collection<int, string>}}
     */
    private function mahasiswaCandidates(string $prodiLabel): array
    {
        if ($prodiLabel === '') {
            return [collect(), collect()];
        }

        $candidates = Mahasiswa::query()
            ->where('study_program', $prodiLabel)
            ->orderByDesc('enrollment_year')
            ->orderBy('name')
            ->get(['id', 'nim', 'name', 'faculty', 'study_program', 'enrollment_year']);

        $grouped = $candidates->groupBy(fn ($m) => $this->angkatanLabel($m->enrollment_year));

        return [$grouped, $grouped->keys()->values()];
    }

    /**
     * Label angkatan dari enrollment_year SSO (format "YYYY1" -> "Angkatan YYYY").
     */
    private function angkatanLabel(?string $enrollmentYear): string
    {
        if ($enrollmentYear === null || trim($enrollmentYear) === '') {
            return 'Tanpa Angkatan';
        }

        $year = substr(trim($enrollmentYear), 0, 4);

        return preg_match('/^\d{4}$/', $year) === 1 ? "Angkatan {$year}" : "Angkatan {$enrollmentYear}";
    }

    private function greeting(): string
    {
        $hour = (int) now()->format('G');

        return match (true) {
            $hour >= 5 && $hour < 12 => 'Selamat pagi',
            $hour >= 12 && $hour < 15 => 'Selamat siang',
            $hour >= 15 && $hour < 18 => 'Selamat sore',
            default => 'Selamat malam',
        };
    }
}
