<?php

use Illuminate\Support\Facades\Http;

use function Pest\Laravel\get;

beforeEach(function () {
    Http::fake([
        'api.github.com/*' => Http::response([], 404),
    ]);
});

test('changelog page loads', function () {
    $response = get('/changelog');

    $response->assertOk();
});

test('changelog displays version entries', function () {
    $response = get('/changelog');

    $response->assertSee('1.4.0');
    $response->assertSee('1.0.0');
});

test('changelog displays entry titles', function () {
    $response = get('/changelog');

    $response->assertSee('KneadIt Launch');
});

test('changelog config has entries', function () {
    $entries = config('changelog');

    expect($entries)->not->toBeEmpty()->and($entries[0])->toHaveKeys(['date', 'version', 'title', 'items']);
});

test('changelog link in footer', function () {
    $layout = file_get_contents(resource_path('views/blog/layout.blade.php'));

    expect($layout)->toContain('/changelog');
});
