<?php

use App\ValueObjects\Money;

test('money can be created from cents', function () {
    $money = Money::fromCents(1500);

    expect($money->cents())->toBe(1500)
        ->and($money->dollars())->toBe(15.00)
        ->and($money->formatted())->toBe('$15.00');
});

test('money can be created from dollars', function () {
    $money = Money::fromDollars(25.99);

    expect($money->cents())->toBe(2599)
        ->and($money->dollars())->toBe(25.99);
});

test('money arithmetic works correctly', function () {
    $a = Money::fromDollars(10.00);
    $b = Money::fromDollars(3.50);

    expect($a->add($b)->dollars())->toBe(13.50)
        ->and($a->subtract($b)->dollars())->toBe(6.50)
        ->and($a->multiply(2)->dollars())->toBe(20.00);
});

test('money comparisons work correctly', function () {
    $a = Money::fromDollars(10.00);
    $b = Money::fromDollars(5.00);
    $zero = Money::fromCents(0);

    expect($a->greaterThan($b))->toBeTrue()
        ->and($b->greaterThan($a))->toBeFalse()
        ->and($zero->isZero())->toBeTrue()
        ->and($a->isPositive())->toBeTrue();
});

test('money avoids float precision issues', function () {
    // 0.1 + 0.2 != 0.3 in floats, but Money handles it
    $a = Money::fromDollars(0.10);
    $b = Money::fromDollars(0.20);

    expect($a->add($b)->dollars())->toBe(0.30);
});

test('money __toString returns formatted currency', function () {
    $money = Money::fromDollars(42.50);

    expect((string) $money)->toBe('$42.50');
});

test('money fromDollars accepts string input', function () {
    $money = Money::fromDollars('19.99');

    expect($money->cents())->toBe(1999)
        ->and($money->dollars())->toBe(19.99);
});

test('money zero returns zero-value Money instance', function () {
    $zero = Money::zero();

    expect($zero->cents())->toBe(0)
        ->and($zero->isZero())->toBeTrue();
});

test('money equals compares cents exactly', function () {
    $a = Money::fromDollars(10.00);
    $b = Money::fromDollars(10.00);
    $c = Money::fromCents(1001);

    expect($a->equals($b))->toBeTrue()
        ->and($a->equals($c))->toBeFalse();
});

test('money min returns the smaller of two values', function () {
    $small = Money::fromDollars(5.00);
    $big = Money::fromDollars(10.00);

    expect($small->min($big))->toBe($small)
        ->and($big->min($small))->toBe($small)
        ->and($small->min($small))->toBe($small);
});

test('money max returns the larger of two values', function () {
    $small = Money::fromDollars(5.00);
    $big = Money::fromDollars(10.00);

    expect($small->max($big))->toBe($big)
        ->and($big->max($small))->toBe($big);
});
