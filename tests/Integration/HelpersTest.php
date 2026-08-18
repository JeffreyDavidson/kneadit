<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('settings helper can set and get values', function () {
    settings(['test_key' => 'test_value']);

    expect(settings('test_key'))->toBe('test_value');
});

test('settings helper returns default for missing key', function () {
    expect(settings('nonexistent', 'default_val'))->toBe('default_val');
});
