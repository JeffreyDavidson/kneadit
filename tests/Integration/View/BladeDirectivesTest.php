<?php

use Illuminate\Support\Facades\Blade;

test('money directive formats amount with dollar sign and two decimals', function () {
    $result = Blade::render('@money(12.5)');

    expect($result)->toBe('$12.50');
});

test('money directive formats whole number', function () {
    $result = Blade::render('@money(100)');

    expect($result)->toBe('$100.00');
});

test('money directive formats zero', function () {
    $result = Blade::render('@money(0)');

    expect($result)->toBe('$0.00');
});

test('money directive formats variable amount', function () {
    $result = Blade::render('@money($amount)', ['amount' => 9.99]);

    expect($result)->toBe('$9.99');
});

test('money directive formats large amount with comma separator', function () {
    $result = Blade::render('@money(1234.5)');

    expect($result)->toBe('$1,234.50');
});
