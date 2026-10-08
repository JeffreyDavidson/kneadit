<?php

use App\ValueObjects\Money;
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

test('money directive puts the minus sign before the dollar sign', function () {
    $result = Blade::render('@money(-461.85)');

    expect($result)->toBe('-$461.85');
});

test('money directive formats variable amount', function () {
    $result = Blade::render('@money($amount)', ['amount' => 9.99]);

    expect($result)->toBe('$9.99');
});

test('money directive formats large amount with comma separator', function () {
    $result = Blade::render('@money(1234.5)');

    expect($result)->toBe('$1,234.50');
});

test('money directive handles Money value object instances', function () {
    $result = Blade::render('@money($amount)', ['amount' => Money::fromDollars(42.50)]);

    expect($result)->toBe('$42.50');
});

test('money directive handles Money zero instance', function () {
    $result = Blade::render('@money($amount)', ['amount' => Money::zero()]);

    expect($result)->toBe('$0.00');
});

test('phone directive shows a stored number the way a person reads it', function (?string $stored, string $shown) {
    $result = Blade::render('@phone($phone)', ['phone' => $stored]);

    expect($result)->toBe($shown);
})->with([
    'US number at a US bakery' => ['+19133877359', '(913) 387-7359'],
    'UK number at a US bakery' => ['+442079460958', '+44 20 7946 0958'],
    'unreadable value as stored' => ['555-0100', '555-0100'],
    'nothing' => [null, ''],
]);

test('phone directive escapes what it prints', function () {
    $result = Blade::render('@phone($phone)', ['phone' => '<b>call</b>']);

    expect($result)->toBe('&lt;b&gt;call&lt;/b&gt;');
});
