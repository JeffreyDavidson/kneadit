<?php

beforeEach(function () {
    setUpCentralTest();
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

    expect($entries)->not->toBeEmpty();
    expect($entries[0])->toHaveKey('date');
    expect($entries[0])->toHaveKey('version');
    expect($entries[0])->toHaveKey('title');
    expect($entries[0])->toHaveKey('items');
});

test('changelog link in footer', function () {
    $layout = file_get_contents(resource_path('views/blog/layout.blade.php'));

    expect($layout)->toContain('/changelog');
});
