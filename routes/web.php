<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LandingKegiatanController;
use App\Http\Controllers\LandingStructureController;
use App\Http\Controllers\OrganizationSocialController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\Simora\AccountController;
use App\Http\Controllers\Simora\DuesController;
use App\Http\Controllers\Simora\BudgetController;
use App\Http\Controllers\Simora\RealizationController;
use App\Http\Controllers\Simora\CashTransactionController;
use App\Http\Controllers\Simora\EProgressController;
use App\Http\Controllers\Simora\LandingManagementController;
use App\Http\Controllers\Simora\OrganizationController;
use App\Http\Controllers\Simora\OrganizationRegistrationController;
use App\Http\Controllers\Simora\OrganizationVisionMissionController;
use App\Http\Controllers\Simora\OrgmateController;
use App\Http\Controllers\Simora\ActivityController;
use App\Http\Controllers\Simora\AnnouncementController;
use App\Http\Controllers\Simora\AttendanceController;
use App\Http\Controllers\Simora\CommitteeController;
use App\Http\Controllers\Simora\DocumentationController;
use App\Http\Controllers\Simora\EvaluationController;
use App\Http\Controllers\Simora\ArchiveController;
use App\Http\Controllers\Simora\MemberController;
use App\Http\Controllers\Simora\PositionController;
use App\Http\Controllers\Simora\SentraController;
use App\Http\Controllers\Simora\WorkProgramController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('home');
Route::get('/kontak', ContactController::class)->name('kontak');
Route::get('/struktur', [LandingStructureController::class, 'index'])->name('struktur.index');
Route::get('/struktur/{organization:slug}', [LandingStructureController::class, 'show'])->name('struktur.show');
Route::get('/kegiatan', [LandingKegiatanController::class, 'index'])->name('kegiatan.index');
Route::get('/kegiatan/{activity}', [LandingKegiatanController::class, 'show'])->name('kegiatan.show');

Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::get('/panel', PanelController::class)
    ->middleware(['auth', 'role:developer,organization'])->name('panel');

Route::middleware(['auth', 'role:developer,organization'])->prefix('simora')->name('simora.')->group(function () {
    Route::get('/orgmate', [OrgmateController::class, 'dashboard'])->name('orgmate');
    Route::get('/orgmate/panduan', [OrgmateController::class, 'panduan'])->name('orgmate.panduan');
    Route::get('/orgmate/struktur', [OrgmateController::class, 'struktur'])->name('orgmate.struktur');
    Route::get('/orgmate/anggota', [OrgmateController::class, 'anggota'])->name('orgmate.anggota');
    Route::post('/orgmate/anggota', [MemberController::class, 'store'])->middleware('role:organization')->name('orgmate.anggota.store');
    Route::put('/orgmate/anggota/{member}', [MemberController::class, 'update'])->middleware('role:organization')->name('orgmate.anggota.update');
    Route::delete('/orgmate/anggota/{member}', [MemberController::class, 'destroy'])->middleware('role:organization')->name('orgmate.anggota.destroy');
    Route::get('/orgmate/rekrutmen', [OrgmateController::class, 'rekrutmen'])->name('orgmate.rekrutmen');
    Route::post('/orgmate/pengumuman', [AnnouncementController::class, 'store'])->middleware('role:organization')->name('orgmate.pengumuman.store');
    Route::put('/orgmate/pengumuman/{announcement}', [AnnouncementController::class, 'update'])->middleware('role:organization')->name('orgmate.pengumuman.update');
    Route::delete('/orgmate/pengumuman/{announcement}', [AnnouncementController::class, 'destroy'])->middleware('role:organization')->name('orgmate.pengumuman.destroy');
    Route::post('/orgmate/arsip', [ArchiveController::class, 'store'])->middleware('role:organization')->name('orgmate.arsip.store');
    Route::put('/orgmate/arsip/{archive}', [ArchiveController::class, 'update'])->middleware('role:organization')->name('orgmate.arsip.update');
    Route::delete('/orgmate/arsip/{archive}', [ArchiveController::class, 'destroy'])->middleware('role:organization')->name('orgmate.arsip.destroy');
    Route::post('/orgmate/struktur', [PositionController::class, 'store'])->middleware('role:organization')->name('orgmate.struktur.store');
    Route::put('/orgmate/struktur/{position}', [PositionController::class, 'update'])->middleware('role:organization')->name('orgmate.struktur.update');
    Route::delete('/orgmate/struktur/{position}', [PositionController::class, 'destroy'])->middleware('role:organization')->name('orgmate.struktur.destroy');
    Route::get('/orgmate/arsip', [OrgmateController::class, 'arsip'])->name('orgmate.arsip');
    Route::get('/orgmate/pengumuman', [OrgmateController::class, 'pengumuman'])->name('orgmate.pengumuman');
    Route::get('/e-progress', [EProgressController::class, 'dashboard'])->name('e-progress');
    Route::get('/e-progress/panduan', [EProgressController::class, 'panduan'])->name('e-progress.panduan');
    Route::get('/e-progress/proker', [WorkProgramController::class, 'index'])->name('e-progress.proker');
    Route::get('/e-progress/kepanitiaan', [EProgressController::class, 'kepanitiaan'])->name('e-progress.kepanitiaan');
    Route::get('/e-progress/monitoring', [EProgressController::class, 'monitoring'])->name('e-progress.monitoring');
    Route::get('/e-progress/evaluasi', [EProgressController::class, 'evaluasi'])->name('e-progress.evaluasi');
    Route::post('/e-progress/evaluasi', [EvaluationController::class, 'store'])->middleware('role:organization')->name('e-progress.evaluasi.store');
    Route::put('/e-progress/evaluasi/{evaluation}', [EvaluationController::class, 'update'])->middleware('role:organization')->name('e-progress.evaluasi.update');
    Route::delete('/e-progress/evaluasi/{evaluation}', [EvaluationController::class, 'destroy'])->middleware('role:organization')->name('e-progress.evaluasi.destroy');
    Route::get('/e-progress/dokumentasi', [EProgressController::class, 'dokumentasi'])->name('e-progress.dokumentasi');
    Route::post('/e-progress/dokumentasi', [DocumentationController::class, 'store'])->middleware('role:organization')->name('e-progress.dokumentasi.store');
    Route::put('/e-progress/dokumentasi/{documentation}', [DocumentationController::class, 'update'])->middleware('role:organization')->name('e-progress.dokumentasi.update');
    Route::delete('/e-progress/dokumentasi/{documentation}', [DocumentationController::class, 'destroy'])->middleware('role:organization')->name('e-progress.dokumentasi.destroy');
    Route::get('/e-progress/presensi', [EProgressController::class, 'presensi'])->name('e-progress.presensi');
    Route::post('/e-progress/presensi', [AttendanceController::class, 'store'])->middleware('role:organization')->name('e-progress.presensi.store');
    Route::delete('/e-progress/presensi/{attendance}', [AttendanceController::class, 'destroy'])->middleware('role:organization')->name('e-progress.presensi.destroy');
    Route::post('/e-progress/kepanitiaan', [CommitteeController::class, 'store'])->middleware('role:organization')->name('e-progress.kepanitiaan.store');
    Route::put('/e-progress/kepanitiaan/{committee}', [CommitteeController::class, 'update'])->middleware('role:organization')->name('e-progress.kepanitiaan.update');
    Route::delete('/e-progress/kepanitiaan/{committee}', [CommitteeController::class, 'destroy'])->middleware('role:organization')->name('e-progress.kepanitiaan.destroy');
    Route::get('/e-progress/kalender', [EProgressController::class, 'kalender'])->name('e-progress.kalender');
    Route::post('/e-progress/kalender', [ActivityController::class, 'store'])->middleware('role:organization')->name('e-progress.kalender.store');
    Route::put('/e-progress/kalender/{activity}', [ActivityController::class, 'update'])->middleware('role:organization')->name('e-progress.kalender.update');
    Route::delete('/e-progress/kalender/{activity}', [ActivityController::class, 'destroy'])->middleware('role:organization')->name('e-progress.kalender.destroy');
    Route::post('/e-progress/proker', [WorkProgramController::class, 'store'])->middleware('role:organization')->name('e-progress.proker.store');
    Route::put('/e-progress/proker/{workProgram}', [WorkProgramController::class, 'update'])->middleware('role:organization')->name('e-progress.proker.update');
    Route::delete('/e-progress/proker/{workProgram}', [WorkProgramController::class, 'destroy'])->middleware('role:organization')->name('e-progress.proker.destroy');
    Route::get('/sentra', [SentraController::class, 'dashboard'])->name('sentra');
    Route::get('/sentra/kas', [SentraController::class, 'kas'])->name('sentra.kas');
    Route::post('/sentra/kas', [CashTransactionController::class, 'store'])->middleware('role:organization')->name('sentra.kas.store');
    Route::put('/sentra/kas/{cashTransaction}', [CashTransactionController::class, 'update'])->middleware('role:organization')->name('sentra.kas.update');
    Route::delete('/sentra/kas/{cashTransaction}', [CashTransactionController::class, 'destroy'])->middleware('role:organization')->name('sentra.kas.destroy');
    Route::get('/sentra/rab', [SentraController::class, 'rab'])->name('sentra.rab');
    Route::post('/sentra/rab', [BudgetController::class, 'store'])->middleware('role:organization')->name('sentra.rab.store');
    Route::put('/sentra/rab/{budget}', [BudgetController::class, 'update'])->middleware('role:organization')->name('sentra.rab.update');
    Route::delete('/sentra/rab/{budget}', [BudgetController::class, 'destroy'])->middleware('role:organization')->name('sentra.rab.destroy');
    Route::get('/sentra/iuran', [SentraController::class, 'iuran'])->name('sentra.iuran');
    Route::post('/sentra/iuran', [DuesController::class, 'store'])->middleware('role:organization')->name('sentra.iuran.store');
    Route::put('/sentra/iuran/{due}', [DuesController::class, 'update'])->middleware('role:organization')->name('sentra.iuran.update');
    Route::delete('/sentra/iuran/{due}', [DuesController::class, 'destroy'])->middleware('role:organization')->name('sentra.iuran.destroy');
    Route::put('/sentra/iuran/{due}/lunas', [DuesController::class, 'markPaid'])->middleware('role:organization')->name('sentra.iuran.markPaid');
    Route::get('/sentra/realisasi', [SentraController::class, 'realisasi'])->name('sentra.realisasi');
    Route::post('/sentra/realisasi', [RealizationController::class, 'store'])->middleware('role:organization')->name('sentra.realisasi.store');
    Route::get('/sentra/laporan', [SentraController::class, 'laporan'])->name('sentra.laporan');
    Route::get('/sentra/panduan', [SentraController::class, 'panduan'])->name('sentra.panduan');
    Route::view('/kalender-kerja', 'simora.organizations.kalender-kerja')
        ->middleware('role:organization')
        ->name('kalender-kerja');
});

Route::resource('/simora/organisasi', OrganizationController::class)
    ->except(['show'])
    ->parameters(['organisasi' => 'organization'])
    ->middleware(['auth', 'role:developer'])
    ->names('simora.organizations');

Route::resource('/simora/akun', AccountController::class)
    ->except(['show'])
    ->parameters(['akun' => 'account'])
    ->middleware(['auth', 'role:developer'])
    ->names('simora.accounts');

Route::put('/simora/organisasi-saya/pendaftaran', [OrganizationRegistrationController::class, 'update'])
    ->middleware(['auth', 'role:organization'])
    ->name('simora.my-registration.update');

Route::delete('/simora/organisasi-saya/pendaftaran', [OrganizationRegistrationController::class, 'destroy'])
    ->middleware(['auth', 'role:organization'])
    ->name('simora.my-registration.destroy');

Route::put('/simora/organisasi-saya/sosmed', [OrganizationSocialController::class, 'update'])
    ->middleware(['auth', 'role:organization'])
    ->name('simora.my-socials.update');

Route::put('/simora/organisasi-saya/visi-misi', [OrganizationVisionMissionController::class, 'update'])
    ->middleware(['auth', 'role:organization'])
    ->name('simora.my-vision-mission.update');

Route::get('/simora/kelola-landing', [LandingManagementController::class, 'edit'])
    ->middleware(['auth', 'role:developer'])
    ->name('simora.landing-management');

Route::put('/simora/kelola-landing', [LandingManagementController::class, 'update'])
    ->middleware(['auth', 'role:developer'])
    ->name('simora.landing-management.update');

// Presensi publik via QR code (signed URL, tanpa login — peserta hanya scan & isi nama)
Route::get('/presensi/{activity}', [AttendanceController::class, 'checkin'])
    ->middleware('signed')
    ->name('presensi.checkin');
Route::post('/presensi/{activity}', [AttendanceController::class, 'checkinStore'])
    ->middleware('signed')
    ->name('presensi.checkin.store');
