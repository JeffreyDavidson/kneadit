<?php

use function Pest\Laravel\get;

beforeEach(function () {
    setUpCentralTest();
});

// --- #24: Custom 404 page ---

test('404 page exists', function () {
    expect(file_exists(resource_path('views/errors/404.blade.php')))->toBeTrue('Custom 404 view should exist');
});

test('404 page contains bakery themed copy', function () {
    $content = file_get_contents(resource_path('views/errors/404.blade.php'));

    expect($content)->toContain("__('errors.404.heading')")->toContain('404');
});

test('nonexistent route returns 404', function () {
    $response = get('/this-page-does-not-exist-at-all');

    $response->assertNotFound();
});

// --- #22: OG meta tags ---

test('storefront layout has og tags', function () {
    $layout = file_get_contents(resource_path('views/components/layouts/storefront.blade.php'));

    expect($layout)->toContain('og:title')->toContain('og:description')->toContain('og:type')->toContain('og:url')->toContain('og:site_name')->toContain('twitter:card');
});

test('storefront layout has meta description', function () {
    $layout = file_get_contents(resource_path('views/components/layouts/storefront.blade.php'));

    expect($layout)->toContain('meta name="description"');
});

// --- #21: Dynamic favicon ---

test('storefront layout has favicon', function () {
    $layout = file_get_contents(resource_path('views/components/layouts/storefront.blade.php'));

    expect($layout)->toContain('rel="icon"');
});

test('favicon uses store logo when available', function () {
    $layout = file_get_contents(resource_path('views/components/layouts/storefront.blade.php'));

    expect($layout)->toContain('$settings->store->logo');
});

test('favicon falls back to svg with brand color', function () {
    $layout = file_get_contents(resource_path('views/components/layouts/storefront.blade.php'));

    expect($layout)->toContain('image/svg+xml')->toContain('brand_color_primary');
});

// --- #23: Cookie consent ---

test('storefront layout has cookie consent banner', function () {
    $layout = file_get_contents(resource_path('views/components/layouts/storefront.blade.php'));

    expect($layout)->toContain('cookieConsent')->toContain('acceptCookies')->toContain('localStorage');
});

test('cookie consent links to privacy policy', function () {
    $layout = file_get_contents(resource_path('views/components/layouts/storefront.blade.php'));

    expect($layout)->toContain('/privacy');
});

test('cookie consent hidden by default', function () {
    $layout = file_get_contents(resource_path('views/components/layouts/storefront.blade.php'));

    expect($layout)->toMatch('/id="cookieConsent"[^>]*class="[^"]*\bhidden\b/');
});
