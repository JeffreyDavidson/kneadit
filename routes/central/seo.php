<?php

use App\Http\Controllers\Central\SitemapController;
use App\Http\Middleware\PreventAccessFromTenantDomains;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

// The sitemap and robots file describe the marketing site, so a bakery host does not serve them.
Route::middleware(PreventAccessFromTenantDomains::class)->group(function () {
    Route::get('sitemap.xml', SitemapController::class)->name('sitemap');
    Route::get('robots.txt', fn () => response("User-agent: *\nAllow: /\n\nSitemap: ".URL::route('sitemap')."\n", 200, ['Content-Type' => 'text/plain']))->name('robots');
});
