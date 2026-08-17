<?php

use App\Casts\MoneyCentsCast;
use App\ValueObjects\Money;
use Illuminate\Database\Eloquent\Model;

test('get returns null for null storage', function () {
    $cast = new MoneyCentsCast;
    $model = new class extends Model {};

    expect($cast->get($model, 'amount', null, []))->toBeNull();
});

test('get hydrates int cents into Money', function () {
    $cast = new MoneyCentsCast;
    $model = new class extends Model {};

    $money = $cast->get($model, 'amount', 1599, []);

    if ($money === null) {
        throw new RuntimeException('Expected the cast to hydrate Money.');
    }

    expect($money)->toBeInstanceOf(Money::class)
        ->and($money->cents())->toBe(1599)
        ->and($money->dollars())->toBe(15.99);
});

test('get coerces string ints (PDO sometimes hands back strings)', function () {
    $cast = new MoneyCentsCast;
    $model = new class extends Model {};

    $money = $cast->get($model, 'amount', '2550', []);

    if ($money === null) {
        throw new RuntimeException('Expected the cast to hydrate Money.');
    }

    expect($money->cents())->toBe(2550);
});

test('set returns null for null', function () {
    $cast = new MoneyCentsCast;
    $model = new class extends Model {};

    expect($cast->set($model, 'amount', null, []))->toBeNull();
});

test('set serialises Money as int cents', function () {
    $cast = new MoneyCentsCast;
    $model = new class extends Model {};

    $result = $cast->set($model, 'amount', Money::fromDollars(42.50), []);

    expect($result)->toBe(4250);
});

test('set treats raw float as dollars (factory backward-compat)', function () {
    $cast = new MoneyCentsCast;
    $model = new class extends Model {};

    $result = $cast->set($model, 'amount', 19.99, []);

    expect($result)->toBe(1999);
});

test('roundtrips Money through set + get', function () {
    $cast = new MoneyCentsCast;
    $model = new class extends Model {};

    $original = Money::fromDollars(99.99);
    $stored = $cast->set($model, 'amount', $original, []);
    $retrieved = $cast->get($model, 'amount', $stored, []);

    if ($retrieved === null) {
        throw new RuntimeException('Expected the cast to hydrate Money.');
    }

    expect($retrieved->equals($original))->toBeTrue();
});
