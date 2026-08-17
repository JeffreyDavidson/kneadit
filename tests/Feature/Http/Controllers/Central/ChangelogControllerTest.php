<?php

use Illuminate\Support\Facades\Http;

use function Pest\Laravel\get;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);

    Http::fake([
        'api.github.com/*' => Http::response([], 404),
    ]);
});

test('changelog page renders', function () {
    get(route('changelog'))->assertOk();
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

    throw_unless(is_array($entries), UnexpectedValueException::class, 'Expected changelog entries.');

    expect($entries)->not->toBeEmpty()->and($entries[0])->toHaveKeys(['date', 'version', 'title', 'items']);
});

test('changelog link in footer', function () {
    $layout = file_get_contents(resource_path('views/central/blog/layout.blade.php'));

    expect($layout)->toContain('/changelog');
});
