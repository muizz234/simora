<?php

use App\Http\Controllers\Api\V1\OrganizationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
