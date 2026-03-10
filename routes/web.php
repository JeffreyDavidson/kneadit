<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/billing.php';
require __DIR__.'/admin.php';

/*
|--------------------------------------------------------------------------
| Central Routes
|--------------------------------------------------------------------------
|
| These routes are for the central app: landing page, registration,
| billing, and tenant onboarding. Storefront routes live in tenant.php.
|
*/

// Impersonation (central admin → tenant)
Route::get('/impersonate/{tenant}', [\App\Http\Controllers\ImpersonateController::class, 'login'])
    ->name('tenant.impersonate')
    ->middleware('signed');

// Referral tracking
Route::get('/ref/{code}', [\App\Http\Controllers\ReferralController::class, 'track'])->name('referral.track');

// Root route — serves landing page on central domains, storefront on tenant subdomains
Route::get('/', [\App\Http\Controllers\RootController::class, 'index'])->name('home');

// Public bakery directory
Route::get('/directory', [\App\Http\Controllers\DirectoryController::class, 'index'])->name('directory');

// Tenant Registration (onboarding)
Route::middleware(['web', 'auth'])->prefix('onboarding')->name('onboarding.')->group(function () {
    Route::get('/', [\App\Http\Controllers\OnboardingController::class, 'show'])->name('show');
    Route::post('/', [\App\Http\Controllers\OnboardingController::class, 'store'])->name('store');
});
