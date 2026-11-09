<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Announcement;
use App\Models\Archive;
use App\Models\Attendance;
use App\Models\Budget;
use App\Models\CashTransaction;
use App\Models\Committee;
use App\Models\Documentation;
use App\Models\Dues;
use App\Models\Evaluation;
use App\Models\FundRequest;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Position;
use App\Models\Proposal;
use App\Models\Recruitment;
use App\Models\User;
use App\Models\WorkProgram;
use Illuminate\Database\Seeder;

class ModuleDemoSeeder extends Seeder
{
    /**
     * Data demo terintegrasi untuk organisasi pertama (HIMA-TI):
     * proker E-Progress terhubung ke anggaran SENTRA, kegiatan terhubung
     * ke proposal/panitia/presensi/dokumentasi/evaluasi, anggota terhubung
     * ke iuran dan jabatan.
     * Aman dijalankan ulang: dilewati bila data sudah ada.
     */
    public function run(): void
    {
        $organization = Organization::query()->orderBy('sort_order')->first();

        if ($organization === null) {
            return;
        }

        $account = $organization->users()->where('role', User::ROLE_ORGANIZATION)->first();

        if ($organization->positions()->exists()) {
            return;
        }

        // --- ORGMATE ---
        $ketuaMember = Member::create([
            'organization_id' => $organization->id,
            'name' => 'Ketua Umum Contoh',
            'nim' => '2301010001',
            'status' => 'aktif',
        ]);
        Member::create([
            'organization_id' => $organization->id,
            'name' => 'Anggota Contoh',
            'nim' => '2301010002',
            'status' => 'aktif',
        ]);

        Position::create([
            'organization_id' => $organization->id,
            'member_id' => $ketuaMember->id,
            'name' => 'Ketua Umum',
            'sort_order' => 1,
        ]);
        Position::create(['organization_id' => $organization->id, 'name' => 'Wakil Ketua Umum', 'sort_order' => 2]);
        Position::create(['organization_id' => $organization->id, 'name' => 'Sekretaris Umum', 'sort_order' => 3]);
        Position::create(['organization_id' => $organization->id, 'name' => 'Bendahara Umum', 'sort_order' => 4]);
        Position::create(['organization_id' => $organization->id, 'name' => 'Koordinator Divisi Humas', 'sort_order' => 5]);

        Archive::create([
            'organization_id' => $organization->id,
            'uploaded_by' => $account?->id,
            'title' => 'SK Kepengurusan Periode Ini',
            'type' => Archive::TYPE_SK,
            'number' => '001/SK/FT/2026',
            'date' => now()->subMonth(),
        ]);

        Announcement::create([
            'organization_id' => $organization->id,
            'created_by' => $account?->id,
            'title' => 'Rapat Internal Bulan Ini',
            'content' => 'Rapat internal akan dilaksanakan pekan depan. Seluruh pengurus wajib hadir.',
            'published_at' => now(),
        ]);

        Recruitment::create([
            'organization_id' => $organization->id,
            'created_by' => $account?->id,
            'title' => 'Open Recruitment Staf Divisi',
            'division' => 'Humas',
            'description' => 'Rekrutmen staf divisi humas untuk periode berjalan.',
            'deadline' => now()->addMonth(),
            'status' => Recruitment::STATUS_OPEN,
        ]);

        // --- E-PROGRESS ---
        $program = WorkProgram::create([
            'organization_id' => $organization->id,
            'created_by' => $account?->id,
            'name' => 'Program Kerja Tahunan',
            'status' => 'pelaksanaan',
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
        ]);

        $activity = Activity::create([
            'organization_id' => $organization->id,
            'work_program_id' => $program->id,
            'created_by' => $account?->id,
            'name' => 'Pelatihan Kepemimpinan',
            'location' => 'Aula FT UNUJA',
            'start_at' => now()->addWeek(),
            'end_at' => now()->addWeek()->addHours(3),
            'status' => 'pelaksanaan',
        ]);

        Proposal::create([
            'organization_id' => $organization->id,
            'activity_id' => $activity->id,
            'submitted_by' => $account?->id,
            'title' => 'Proposal Pelatihan Kepemimpinan',
            'status' => Proposal::STATUS_PENDING,
        ]);
        Proposal::create([
            'organization_id' => $organization->id,
            'activity_id' => $activity->id,
            'submitted_by' => $account?->id,
            'title' => 'Proposal Baksos',
            'status' => Proposal::STATUS_APPROVED,
            'decided_by' => $account?->id,
            'decided_at' => now()->subDays(2),
        ]);

        Committee::create([
            'organization_id' => $organization->id,
            'activity_id' => $activity->id,
            'user_id' => $account?->id,
            'name' => $account?->name ?? 'Ketua Panitia',
            'role' => 'ketua',
        ]);
        Committee::create([
            'organization_id' => $organization->id,
            'activity_id' => $activity->id,
            'name' => 'Sekretaris Panitia',
            'role' => 'sekretaris',
        ]);

        Attendance::create([
            'organization_id' => $organization->id,
            'activity_id' => $activity->id,
            'name' => $account?->name ?? 'Peserta',
            'checked_at' => now()->subDay(),
            'method' => 'qr',
        ]);

        Documentation::create([
            'organization_id' => $organization->id,
            'activity_id' => $activity->id,
            'uploaded_by' => $account?->id,
            'title' => 'Foto Kegiatan',
            'type' => 'foto',
        ]);

        Evaluation::create([
            'organization_id' => $organization->id,
            'activity_id' => $activity->id,
            'created_by' => $account?->id,
            'title' => 'LPJ Pelatihan Kepemimpinan',
            'type' => Evaluation::TYPE_REPORT,
            'status' => 'draft',
        ]);

        // --- SENTRA ---
        CashTransaction::create([
            'organization_id' => $organization->id,
            'created_by' => $account?->id,
            'type' => CashTransaction::TYPE_INCOME,
            'title' => 'Iuran Anggota',
            'amount' => 1500000,
            'date' => now()->subDays(5)->toDateString(),
        ]);
        CashTransaction::create([
            'organization_id' => $organization->id,
            'created_by' => $account?->id,
            'type' => CashTransaction::TYPE_EXPENSE,
            'title' => 'Belanja Konsumsi Pelatihan',
            'amount' => 450000,
            'date' => now()->subDays(3)->toDateString(),
        ]);

        // Anggaran terhubung ke proker E-Progress (integrasi antar modul).
        Budget::create([
            'organization_id' => $organization->id,
            'work_program_id' => $program->id,
            'program_name' => $program->name,
            'year' => (int) now()->format('Y'),
            'allocated' => 5000000,
            'realized' => 1250000,
        ]);

        FundRequest::create([
            'organization_id' => $organization->id,
            'title' => 'Pengajuan Dana Transportasi',
            'amount' => 800000,
            'status' => FundRequest::STATUS_PENDING,
            'applicant' => $account?->name,
        ]);

        Dues::create([
            'organization_id' => $organization->id,
            'user_id' => $account?->id,
            'name' => $account?->name ?? 'Anggota',
            'amount' => 50000,
            'status' => Dues::STATUS_PAID,
            'paid_at' => now()->subDays(4),
            'period' => (string) now()->format('Y'),
        ]);
    }
}
