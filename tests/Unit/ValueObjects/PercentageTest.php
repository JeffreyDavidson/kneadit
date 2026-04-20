<?php

use App\ValueObjects\Money;
use App\ValueObjects\Percentage;

test('percentage can be created from int percent', function () {
    $p = Percentage::fromInt(25);

    expect($p->value())->toBe(25.0)
        ->and($p->decimal())->toBe(0.25)
        ->and($p->formatted())->toBe('25%');
});

test('percentage can be created from float percent', function () {
    $p = Percentage::fromFloat(12.5);

    expect($p->value())->toBe(12.5)
        ->and($p->decimal())->toBe(0.125);
});

test('percentage can be created from string', function () {
    $p = Percentage::fromFloat('7.5');

    expect($p->value())->toBe(7.5);
});

test('percentage can be created from a 0-1 decimal', function () {
    $p = Percentage::fromDecimal(0.15);

    expect($p->value())->toBe(15.0)
        ->and($p->decimal())->toBe(0.15);
});

test('percentage zero returns 0%', function () {
    $p = Percentage::zero();

    expect($p->isZero())->toBeTrue()
        ->and($p->value())->toBe(0.0);
});

test('percentage applyTo multiplies a Money amount', function () {
    $p = Percentage::fromInt(20);
    $amount = Money::fromDollars(50.00);

    expect($p->applyTo($amount)->dollars())->toBe(10.00);
});

test('percentage of multiplies a float', function () {
    $p = Percentage::fromInt(10);

    expect($p->of(250.0))->toBe(25.0);
});

test('percentage formatted accepts precision', function () {
    $p = Percentage::fromFloat(12.5);

    expect($p->formatted(1))->toBe('12.5%')
        ->and($p->formatted())->toBe('13%');
});

test('percentage __toString returns formatted value', function () {
    $p = Percentage::fromInt(42);

    expect((string) $p)->toBe('42%');
});

test('percentage equals compares basis points exactly', function () {
    $a = Percentage::fromFloat(12.5);
    $b = Percentage::fromFloat(12.5);
    $c = Percentage::fromFloat(12.6);

    expect($a->equals($b))->toBeTrue()
        ->and($a->equals($c))->toBeFalse();
});

test('percentage isPositive returns true for non-zero', function () {
    expect(Percentage::fromInt(5)->isPositive())->toBeTrue()
        ->and(Percentage::zero()->isPositive())->toBeFalse();
});

test('percentage greaterThan compares correctly', function () {
    $big = Percentage::fromInt(50);
    $small = Percentage::fromInt(25);

    expect($big->greaterThan($small))->toBeTrue()
        ->and($small->greaterThan($big))->toBeFalse();
});

test('percentage jsonSerialize returns percent value', function () {
    $p = Percentage::fromInt(15);

    expect($p->jsonSerialize())->toBe(15.0)
        ->and(json_encode($p))->toBe('15');
});

test('percentage livewire round-trips', function () {
    $original = Percentage::fromFloat(33.33);
    $restored = Percentage::fromLivewire($original->toLivewire());

    expect($restored->equals($original))->toBeTrue();
});
