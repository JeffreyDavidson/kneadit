<?php

use App\Http\Controllers\Central\ExportController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/billing.php';
require __DIR__.'/admin.php';

// Auth routes (central only)
Route::middleware('web')->group(function () {
    Route::get('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register')->middleware('guest');
    Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'register'])->middleware('guest');
    Route::get('/login', function () { return redirect('/'); })->name('login')->middleware('guest');
    Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout')->middleware('auth');

    // Password reset
    Route::get('/forgot-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request')->middleware('guest');
    Route::post('/forgot-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email')->middleware('guest');
    Route::get('/reset-password/{token}', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset')->middleware('guest');
    Route::post('/reset-password', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update')->middleware('guest');
});

// Data Export (central admin) — uses signed URL to avoid auth middleware redirect issues
Route::get('/admin/export/{tenant}/{type}', [ExportController::class, 'export'])->name('central.export')->middleware('web');

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

// Legal pages
Route::get('/terms', fn () => view('legal.terms'))->name('terms');
Route::get('/privacy', fn () => view('legal.privacy'))->name('privacy');

// SEO
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', fn () => response("User-agent: *\nAllow: /\n\nSitemap: https://getkneadit.app/sitemap.xml\n", 200, ['Content-Type' => 'text/plain']))->name('robots');

// Changelog
Route::get('/changelog', [\App\Http\Controllers\ChangelogController::class, 'index'])->name('changelog');

// Resources / Blog (central only)
Route::get('/resources', [\App\Http\Controllers\BlogController::class, 'index'])->name('blog.index');
Route::get('/resources/feed.xml', [\App\Http\Controllers\BlogController::class, 'feed'])->name('blog.feed');
Route::get('/resources/{slug}', [\App\Http\Controllers\BlogController::class, 'show'])->name('blog.show');

// Root route — serves landing page on central domains, storefront on tenant subdomains
Route::get('/', [\App\Http\Controllers\RootController::class, 'index'])->name('home');

// Public bakery directory
Route::get('/directory', [\App\Http\Controllers\DirectoryController::class, 'index'])->name('directory');

// Tenant Registration (onboarding)
Route::middleware(['web', 'auth'])->prefix('onboarding')->name('onboarding.')->group(function () {
    Route::get('/', [\App\Http\Controllers\OnboardingController::class, 'show'])->name('show');
    Route::post('/', [\App\Http\Controllers\OnboardingController::class, 'store'])->name('store');
});
