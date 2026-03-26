<?php

use App\Services\Analytics\PageViewTracker;

it('detects page from route name', function () {
    $tracker = new PageViewTracker;

    expect($tracker->detectPage('storefront.menu', '/'))->toBe('menu');
    expect($tracker->detectPage('storefront.home', '/'))->toBe('home');
    expect($tracker->detectPage('storefront.about', '/about'))->toBe('about');
    expect($tracker->detectPage(null, ''))->toBe('home');
    expect($tracker->detectPage(null, '/unknown'))->toBeNull();
});
