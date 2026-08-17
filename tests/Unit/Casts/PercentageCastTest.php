<?php

use App\Casts\PercentageCast;
use App\ValueObjects\Percentage;
use Illuminate\Database\Eloquent\Model;

test('percentage cast get returns null for null value', function () {
    $cast = new PercentageCast;
    $model = new class extends Model {};

    expect($cast->get($model, 'pct', null, []))->toBeNull();
});

test('percentage cast get converts int to Percentage', function () {
    $cast = new PercentageCast;
    $model = new class extends Model {};

    $pct = $cast->get($model, 'pct', 25, []);

    if ($pct === null) {
        throw new RuntimeException('Expected the cast to hydrate Percentage.');
    }

    expect($pct)->toBeInstanceOf(Percentage::class)
        ->and($pct->value())->toBe(25.0);
});

test('percentage cast get converts decimal string to Percentage', function () {
    $cast = new PercentageCast;
    $model = new class extends Model {};

    $pct = $cast->get($model, 'pct', '12.5', []);

    if ($pct === null) {
        throw new RuntimeException('Expected the cast to hydrate Percentage.');
    }

    expect($pct->value())->toBe(12.5);
});

test('percentage cast set returns null for null', function () {
    $cast = new PercentageCast;
    $model = new class extends Model {};

    expect($cast->set($model, 'pct', null, []))->toBeNull();
});

test('percentage cast set converts Percentage instance to float', function () {
    $cast = new PercentageCast;
    $model = new class extends Model {};

    $result = $cast->set($model, 'pct', Percentage::fromInt(42), []);

    expect($result)->toBe(42.0);
});

test('percentage cast set accepts raw numeric and returns float', function () {
    $cast = new PercentageCast;
    $model = new class extends Model {};

    expect($cast->set($model, 'pct', 50, []))->toBe(50.0)
        ->and($cast->set($model, 'pct', '7.5', []))->toBe(7.5);
});

test('percentage cast roundtrips Percentage value through set then get', function () {
    $cast = new PercentageCast;
    $model = new class extends Model {};

    $original = Percentage::fromFloat(33.5);
    $stored = $cast->set($model, 'pct', $original, []);
    $retrieved = $cast->get($model, 'pct', $stored, []);

    if ($retrieved === null) {
        throw new RuntimeException('Expected the cast to hydrate Percentage.');
    }

    expect($retrieved->equals($original))->toBeTrue();
});
