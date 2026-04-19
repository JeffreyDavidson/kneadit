<?php

use App\Casts\MoneyCast;
use App\ValueObjects\Money;
use Illuminate\Database\Eloquent\Model;

test('money cast get returns null for null value', function () {
    $cast = new MoneyCast;
    $model = new class extends Model {};

    expect($cast->get($model, 'amount', null, []))->toBeNull();
});

test('money cast get converts decimal string to Money', function () {
    $cast = new MoneyCast;
    $model = new class extends Model {};

    $money = $cast->get($model, 'amount', '15.99', []);

    expect($money)->toBeInstanceOf(Money::class)
        ->and($money->cents())->toBe(1599);
});

test('money cast get converts float to Money', function () {
    $cast = new MoneyCast;
    $model = new class extends Model {};

    $money = $cast->get($model, 'amount', 25.50, []);

    expect($money)->toBeInstanceOf(Money::class)
        ->and($money->cents())->toBe(2550);
});

test('money cast set returns null for null', function () {
    $cast = new MoneyCast;
    $model = new class extends Model {};

    expect($cast->set($model, 'amount', null, []))->toBeNull();
});

test('money cast set converts Money instance to float dollars', function () {
    $cast = new MoneyCast;
    $model = new class extends Model {};

    $result = $cast->set($model, 'amount', Money::fromDollars(42.50), []);

    expect($result)->toBe(42.50);
});

test('money cast set accepts raw float and returns float', function () {
    $cast = new MoneyCast;
    $model = new class extends Model {};

    $result = $cast->set($model, 'amount', 19.99, []);

    expect($result)->toBe(19.99);
});

test('money cast set accepts numeric string and returns float', function () {
    $cast = new MoneyCast;
    $model = new class extends Model {};

    $result = $cast->set($model, 'amount', '7.25', []);

    expect($result)->toBe(7.25);
});

test('money cast roundtrips Money value through set then get', function () {
    $cast = new MoneyCast;
    $model = new class extends Model {};

    $original = Money::fromDollars(99.99);
    $stored = $cast->set($model, 'amount', $original, []);
    $retrieved = $cast->get($model, 'amount', $stored, []);

    expect($retrieved->equals($original))->toBeTrue();
});
