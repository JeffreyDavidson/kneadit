<?php

declare(strict_types=1);

use App\Http\Controllers\Central\MailPreviewsController;
use App\Http\Middleware\EnsureLocalEnvironment;
use App\Http\Middleware\PreventAccessFromTenantDomains;
use Illuminate\Support\Facades\Route;

// Browser previews of KneadIt's own emails. They answer 404 outside a developer's machine.
Route::middleware(['web', EnsureLocalEnvironment::class, PreventAccessFromTenantDomains::class])
    ->prefix('mail-previews')
    ->name('mailPreviews.')
    ->group(function (): void {
        Route::get('/', [MailPreviewsController::class, 'index'])->name('index');
        Route::get('{mail}', [MailPreviewsController::class, 'show'])->name('show');
    });
