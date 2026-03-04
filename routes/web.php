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

// Landing / Marketing
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Tenant Registration (onboarding)
Route::middleware(['web', 'auth'])->prefix('onboarding')->name('onboarding.')->group(function () {
    Route::get('/', [\App\Http\Controllers\OnboardingController::class, 'show'])->name('show');
    Route::post('/', [\App\Http\Controllers\OnboardingController::class, 'store'])->name('store');
});
