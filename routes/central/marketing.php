<?php

use App\Http\Controllers\Central\BlogController;
use App\Http\Controllers\Central\BlogFeedController;
use App\Http\Controllers\Central\ChangelogController;
use App\Http\Controllers\Central\ContactController;
use App\Http\Controllers\Central\DirectoryController;
use App\Http\Controllers\Central\ReferralController;
use App\Http\Controllers\Central\RootController;
use App\Http\Controllers\CspReportController;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;

Route::get('ref/{code}', ReferralController::class)->name('referral.track');
Route::view('pricing', 'central.marketing.pricing')->name('pricing');
Route::view('terms', 'legal.terms')->name('terms');
Route::view('privacy', 'legal.privacy')->name('privacy');
Route::get('changelog', ChangelogController::class)->name('changelog');

Route::get('resources', [BlogController::class, 'index'])->name('blog.index');
Route::get('resources/feed.xml', BlogFeedController::class)->name('blog.feed');
Route::get('resources/{centralPost}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/', RootController::class)->name('home');
Route::post('contact-us', ContactController::class)
    ->middleware(['web', 'throttle:sensitive-write'])
    ->name('marketing.contact');
Route::get('directory', DirectoryController::class)->name('directory');

Route::post('csp-report', CspReportController::class)
    ->middleware(['web', 'throttle:frequent-poll'])
    ->withoutMiddleware(PreventRequestForgery::class)
    ->name('csp.report');
