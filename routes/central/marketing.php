<?php

use App\Http\Controllers\Central\BlogController;
use App\Http\Controllers\Central\BlogFeedController;
use App\Http\Controllers\Central\ChangelogController;
use App\Http\Controllers\Central\ContactController;
use App\Http\Controllers\Central\DirectoryController;
use App\Http\Controllers\Central\ReferralController;
use App\Http\Controllers\Central\RootController;
use App\Http\Controllers\CspReportController;
use App\Http\Middleware\PreventAccessFromTenantDomains;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;

// The marketing site belongs to the central host. A bakery host must not answer
// these: the web group has already put that bakery in context there.
Route::middleware(PreventAccessFromTenantDomains::class)->group(function () {
    Route::get('ref/{code}', ReferralController::class)->name('referral.track');
    Route::view('pricing', 'central.marketing.pricing')->name('pricing');
    Route::view('terms', 'central.legal.terms')->name('terms');
    Route::view('privacy', 'central.legal.privacy')->name('privacy');
    Route::get('changelog', ChangelogController::class)->name('changelog');

    Route::get('resources', [BlogController::class, 'index'])->name('blog.index');
    Route::get('resources/feed.xml', BlogFeedController::class)->name('blog.feed');
    Route::get('resources/{centralPost}', [BlogController::class, 'show'])->name('blog.show');

    Route::post('contact-us', ContactController::class)
        ->middleware(['web', 'throttle:sensitive-write'])
        ->name('marketing.contact');
    Route::get('directory', DirectoryController::class)->name('directory');
});

// These stay open on bakery hosts: "/" serves the bakery's own storefront home, and every
// page's Content-Security-Policy reports violations to /csp-report on its own host.
Route::get('/', RootController::class)->name('home');

Route::post('csp-report', CspReportController::class)
    ->middleware(['web', 'throttle:frequent-poll'])
    ->withoutMiddleware(PreventRequestForgery::class)
    ->name('csp.report');
