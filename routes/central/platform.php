<?php

use App\Http\Controllers\Auth\CompleteOnboardingController;
use App\Http\Controllers\Auth\ShowOnboardingController;
use App\Http\Controllers\Central\BackupDownloadController;
use App\Http\Controllers\Central\ExportController;
use App\Http\Controllers\Central\ImpersonateController;
use App\Http\Controllers\Central\MaintenancePreviewController;
use Illuminate\Support\Facades\Route;

Route::get('admin/export/{tenant}/{type}', ExportController::class)->name('central.export')->middleware('web');
Route::get('admin/maintenance-mode/preview', MaintenancePreviewController::class)
    ->name('central.maintenance-mode.preview')
    ->middleware(['web', 'auth']);
Route::get('admin/backups/{name}/download', BackupDownloadController::class)
    ->name('central.backups.download')
    ->middleware(['web', 'auth']);

// Keep the central impersonation URL distinct from the tenant token consumer.
Route::get('admin/impersonate/{tenant}', ImpersonateController::class)
    ->name('tenant.impersonate')
    ->middleware(['auth', 'signed']);

Route::middleware(['web', 'auth'])->prefix('onboarding')->name('onboarding.')->group(function () {
    Route::get('/', ShowOnboardingController::class)->name('show');
    Route::post('/', CompleteOnboardingController::class)->name('store');
});
