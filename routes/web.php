<?php

use App\Http\Controllers\Auth\CompleteOnboardingController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ShowOnboardingController;
use App\Http\Controllers\Central\BlogController;
use App\Http\Controllers\Central\BlogFeedController;
use App\Http\Controllers\Central\ChangelogController;
use App\Http\Controllers\Central\DirectoryController;
use App\Http\Controllers\Central\ExportController;
use App\Http\Controllers\Central\ImpersonateController;
use App\Http\Controllers\Central\ReferralController;
use App\Http\Controllers\Central\RootController;
use App\Http\Controllers\Central\SitemapController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/billing.php';
require __DIR__ . '/admin.php';

// Auth routes (central only)
Route::middleware('web')->group(function () {
    Route::get('register', [RegisterController::class, 'show'])->name('register')->middleware('guest');
    Route::post('register', [RegisterController::class, 'store'])->middleware(['guest', 'throttle:5,1']);
    Route::get('login', function () {
        return redirect('/');
    })->name('login')->middleware('guest');
    Route::post('logout', LogoutController::class)->name('logout')->middleware('auth');

    // Email verification
    Route::get('email/verify', fn () => view('auth.verify-email'))->middleware('auth')->name('verification.notice');
    Route::get('email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect('/')->with('verified', true);
    })->middleware(['auth', 'signed'])->name('verification.verify');
    Route::post('email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('message', 'Verification link sent!');
    })->middleware(['auth', 'throttle:6,1'])->name('verification.send');

    // Password reset
    Route::get('forgot-password', [ForgotPasswordController::class, 'show'])->name('password.request')->middleware('guest');
    Route::post('forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email')->middleware(['guest', 'throttle:5,1']);
    Route::get('reset-password/{token}', [ResetPasswordController::class, 'show'])->name('password.reset')->middleware('guest');
    Route::post('reset-password', [ResetPasswordController::class, 'store'])->name('password.update')->middleware(['guest', 'throttle:5,1']);
});

// Data Export (central admin) — uses signed URL to avoid auth middleware redirect issues
Route::get('admin/export/{tenant}/{type}', ExportController::class)->name('central.export')->middleware('web');

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
Route::get('impersonate/{tenant}', ImpersonateController::class)
    ->name('tenant.impersonate')
    ->middleware(['auth', 'signed']);

// Referral tracking
Route::get('ref/{code}', ReferralController::class)->name('referral.track');

// Legal pages
Route::get('pricing', fn () => view('platform.pricing'))->name('pricing');
Route::get('terms', fn () => view('legal.terms'))->name('terms');
Route::get('privacy', fn () => view('legal.privacy'))->name('privacy');

// SEO
Route::get('sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('robots.txt', fn () => response("User-agent: *\nAllow: /\n\nSitemap: https://getkneadit.app/sitemap.xml\n", 200, ['Content-Type' => 'text/plain']))->name('robots');

// Changelog
Route::get('changelog', ChangelogController::class)->name('changelog');

// Resources / Blog (central only)
Route::get('resources', [BlogController::class, 'index'])->name('blog.index');
Route::get('resources/feed.xml', BlogFeedController::class)->name('blog.feed');
Route::get('resources/{post}', [BlogController::class, 'show'])->name('blog.show');

// Root route — serves landing page on central domains, storefront on tenant subdomains
Route::get('/', RootController::class)->name('home');

// Public bakery directory
Route::get('directory', DirectoryController::class)->name('directory');

// Tenant Registration (onboarding)
Route::middleware(['web', 'auth'])->prefix('onboarding')->name('onboarding.')->group(function () {
    Route::get('/', ShowOnboardingController::class)->name('show');
    Route::post('/', CompleteOnboardingController::class)->name('store');
});
