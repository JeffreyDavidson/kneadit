<?php

use App\Services\Analytics\PageViewTracker;

it('detects page from route name', function () {
    $tracker = new PageViewTracker;

    expect($tracker->detectPage('storefront.menu', '/'))->toBe('menu')->and($tracker->detectPage('storefront.home', '/'))->toBe('home')->and($tracker->detectPage('storefront.about', '/about'))->toBe('about')->and($tracker->detectPage(null, ''))->toBe('home')->and($tracker->detectPage(null, '/unknown'))->toBeNull();
});
