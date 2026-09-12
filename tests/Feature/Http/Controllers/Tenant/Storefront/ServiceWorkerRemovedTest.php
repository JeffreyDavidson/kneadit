<?php

use function Pest\Laravel\get;

beforeEach(function () {
    setUpCentralTest();
});

test('service worker route returns 404', function () {
    $response = get('/service-worker.js');

    $response->assertNotFound();
});

test('storefront layout does not register service worker', function () {
    $layout = file_get_contents(resource_path('views/components/layouts/storefront.blade.php'));

    expect($layout)->not->toContain('serviceWorker')->not->toContain('service-worker.js');
});
