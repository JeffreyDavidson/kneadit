<?php

use App\Http\Controllers\Central\SitemapController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

Route::get('sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('robots.txt', fn () => response("User-agent: *\nAllow: /\n\nSitemap: " . URL::route('sitemap') . "\n", 200, ['Content-Type' => 'text/plain']))->name('robots');
