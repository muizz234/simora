<?php

use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\Internal\V1\DirectoryController;
use App\Http\Controllers\Api\Internal\V1\EventIngestController;
use App\Http\Controllers\Api\V1\OrganizationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Health check untuk orchestrator / load balancer (tanpa token).
 */
Route::get('/health', HealthController::class)->name('health');

/**
 * API publik SIMORA (read-only, tanpa login).
 * Kontrak untuk landing publik dan calon konsumen Web 1/2/3.
 */
Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::get('/organizations', [OrganizationController::class, 'index'])->name('organizations.index');
    Route::get('/organizations/{organization:slug}', [OrganizationController::class, 'show'])->name('organizations.show');
    Route::get('/organizations/{organization:slug}/structure', [OrganizationController::class, 'structure'])->name('organizations.structure');

    Route::middleware('api.token')->group(function () {
        Route::get('/me', fn (Request $request) => response()->json([
            'id' => $request->user()->id,
            'name' => $request->user()->name,
            'email' => $request->user()->email,
            'role' => $request->user()->role,
            'organization_id' => $request->user()->organization_id,
            'organization' => $request->user()->organization?->name,
            'avatar_url' => $request->user()->avatar_url,
        ]))->name('me');
    });
});

/**
 * Internal API untuk komunikasi antar-modul / antar-service.
 * Wajib menyertakan service token; bukan untuk konsumen publik.
 */
Route::prefix('internal/v1')->name('api.internal.v1.')->middleware(['service.token', 'throttle:internal-api'])->group(function () {
    Route::get('/organizations/{organization}/members', [DirectoryController::class, 'members'])->name('members');
    Route::get('/organizations/{organization}/work-programs', [DirectoryController::class, 'workPrograms'])->name('work-programs');
    Route::get('/organizations/{organization}/lpjs', [DirectoryController::class, 'lpjs'])->name('lpjs');
    Route::get('/lpjs', [DirectoryController::class, 'allLpjs'])->name('lpjs.all');
    Route::get('/organizations/{organization}/budgets', [DirectoryController::class, 'budgets'])->name('budgets');
    Route::get('/organizations/{organization}/dues', [DirectoryController::class, 'dues'])->name('dues');
    Route::get('/organizations/{organization}/cash-transactions', [DirectoryController::class, 'cashTransactions'])->name('cash-transactions');

    // Penerima event dari service lain; event ditandai published agar tidak di-relay balik.
    Route::post('/events', [EventIngestController::class, 'store'])->name('events.ingest');
});
