<?php

use App\Http\Controllers\Auth\CompleteOnboardingController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\SendVerificationNotificationController;
use App\Http\Controllers\Auth\ShowOnboardingController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Central\BlogController;
use App\Http\Controllers\Central\BlogFeedController;
use App\Http\Controllers\Central\ChangelogController;
use App\Http\Controllers\Central\ContactController;
use App\Http\Controllers\Central\DirectoryController;
use App\Http\Controllers\Central\ExportController;
use App\Http\Controllers\Central\ImpersonateController;
use App\Http\Controllers\Central\ReferralController;
use App\Http\Controllers\Central\RootController;
use App\Http\Controllers\Central\SitemapController;
use App\Http\Controllers\CspReportController;
use App\Routing\Resolvers\PublishedBlogPostResolver;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

Route::bind('centralPost', resolve(PublishedBlogPostResolver::class));

require __DIR__ . '/billing.php';
require __DIR__ . '/admin.php';

// Auth routes (central only)
Route::middleware('web')->group(function () {
    Route::get('register', [RegisterController::class, 'show'])->name('register')->middleware('guest');
    Route::post('register', [RegisterController::class, 'store'])->middleware(['guest', 'throttle:sensitive-write']);
    Route::redirect('login', '/')->name('login')->middleware('guest');
    Route::post('logout', LogoutController::class)->name('logout')->middleware('auth');

    // Email verification
    Route::view('email/verify', 'auth.verify-email')->middleware('auth')->name('verification.notice');
    Route::get('email/verify/{id}/{hash}', VerifyEmailController::class)->middleware(['auth', 'signed'])->name('verification.verify');
    Route::post('email/verification-notification', SendVerificationNotificationController::class)->middleware(['auth', 'throttle:verification-resend'])->name('verification.send');

    // Password reset
    Route::get('forgot-password', [ForgotPasswordController::class, 'show'])->name('password.request')->middleware('guest');
    Route::post('forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email')->middleware(['guest', 'throttle:sensitive-write']);
    Route::get('reset-password/{token}', [ResetPasswordController::class, 'show'])->name('password.reset')->middleware('guest');
    Route::post('reset-password', [ResetPasswordController::class, 'store'])->name('password.update')->middleware(['guest', 'throttle:sensitive-write']);
});

// Data Export (central admin) — uses signed URL to avoid auth middleware redirect issues
Route::get('admin/export/{tenant}/{type}', ExportController::class)->name('central.export')->middleware('web');

// Maintenance mode preview — renders the real platform.maintenance view with admin-supplied message/end
// for embedding in the Central admin page's preview iframe.
Route::get('admin/maintenance-mode/preview', App\Http\Controllers\Central\MaintenancePreviewController::class)
    ->name('central.maintenance-mode.preview')
    ->middleware(['web', 'auth']);

// Backup download — streams a zipped backup folder to the admin.
Route::get('admin/backups/{name}/download', App\Http\Controllers\Central\BackupDownloadController::class)
    ->name('central.backups.download')
    ->middleware(['web', 'auth']);

/*
|--------------------------------------------------------------------------
| Central Routes
|--------------------------------------------------------------------------
|
| These routes are for the central app: landing page, registration,
| billing, and tenant onboarding. Storefront routes live in tenant.php.
|
*/

// Impersonation (central admin → tenant). Path is "admin/impersonate" rather
// than just "impersonate" to avoid colliding with the tenant-side
// "impersonate/{token}" consume route registered in routes/tenant.php — both
// match GET /impersonate/* otherwise, and the first-registered wins, so the
// tenant subdomain was 404'ing the consume URL via a failed Tenant binding.
Route::get('admin/impersonate/{tenant}', ImpersonateController::class)
    ->name('tenant.impersonate')
    ->middleware(['auth', 'signed']);

// Referral tracking
Route::get('ref/{code}', ReferralController::class)->name('referral.track');

// Legal pages
Route::view('pricing', 'platform.pricing')->name('pricing');
Route::view('terms', 'legal.terms')->name('terms');
Route::view('privacy', 'legal.privacy')->name('privacy');

// SEO
Route::get('sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('robots.txt', fn () => response("User-agent: *\nAllow: /\n\nSitemap: " . URL::route('sitemap') . "\n", 200, ['Content-Type' => 'text/plain']))->name('robots');

// Changelog
Route::get('changelog', ChangelogController::class)->name('changelog');

// Resources / Blog (central only)
Route::get('resources', [BlogController::class, 'index'])->name('blog.index');
Route::get('resources/feed.xml', BlogFeedController::class)->name('blog.feed');
Route::get('resources/{centralPost}', [BlogController::class, 'show'])->name('blog.show');

// Root route — serves landing page on central domains, storefront on tenant subdomains
Route::get('/', RootController::class)->name('home');

// Central marketing contact form
Route::post('contact-us', ContactController::class)
    ->middleware(['web', 'throttle:sensitive-write'])
    ->name('marketing.contact');

// Public bakery directory
Route::get('directory', DirectoryController::class)->name('directory');

// CSP violation report receiver. Browsers POST here when either CSP mode
// reports a violation. CSRF is excluded because policy reports do not include
// an application token; the route remains rate limited to constrain intake.
Route::post('csp-report', CspReportController::class)
    ->middleware(['web', 'throttle:frequent-poll'])
    ->withoutMiddleware(Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class)
    ->name('csp.report');

// Tenant Registration (onboarding)
Route::middleware(['web', 'auth'])->prefix('onboarding')->name('onboarding.')->group(function () {
    Route::get('/', ShowOnboardingController::class)->name('show');
    Route::post('/', CompleteOnboardingController::class)->name('store');
});
