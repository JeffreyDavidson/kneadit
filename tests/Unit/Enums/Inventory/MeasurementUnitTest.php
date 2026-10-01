<?php

use App\Enums\Inventory\MeasurementUnit;
use App\Enums\Inventory\UnitDimension;

test('keeps the stored string values', function () {
    expect(collect(MeasurementUnit::cases())->pluck('value')->all())->toEqual([
        'oz', 'lbs', 'g', 'kg', 'cups', 'tbsp', 'tsp', 'ml', 'l', 'each', 'dozen',
    ]);
});

test('each case has a label and a dimension', function (MeasurementUnit $unit, UnitDimension $dimension, string $label) {
    expect($unit->dimension())->toBe($dimension)
        ->and($unit->getLabel())->toBe($label);
})->with([
    'ounces' => [MeasurementUnit::Ounces, UnitDimension::Mass, 'Ounces (oz)'],
    'pounds' => [MeasurementUnit::Pounds, UnitDimension::Mass, 'Pounds (lbs)'],
    'grams' => [MeasurementUnit::Grams, UnitDimension::Mass, 'Grams (g)'],
    'kilograms' => [MeasurementUnit::Kilograms, UnitDimension::Mass, 'Kilograms (kg)'],
    'cups' => [MeasurementUnit::Cups, UnitDimension::Volume, 'Cups'],
    'tablespoons' => [MeasurementUnit::Tablespoons, UnitDimension::Volume, 'Tablespoons'],
    'teaspoons' => [MeasurementUnit::Teaspoons, UnitDimension::Volume, 'Teaspoons'],
    'milliliters' => [MeasurementUnit::Milliliters, UnitDimension::Volume, 'Milliliters (ml)'],
    'liters' => [MeasurementUnit::Liters, UnitDimension::Volume, 'Liters (l)'],
    'each' => [MeasurementUnit::Each, UnitDimension::Count, 'Each'],
    'dozen' => [MeasurementUnit::Dozen, UnitDimension::Count, 'Dozen'],
]);

test('converts a quantity between units of the same dimension', function (MeasurementUnit $from, MeasurementUnit $to, float $quantity, float $expected) {
    expect($from->convert($quantity, $to))->toEqualWithDelta($expected, 0.000001);
})->with([
    'grams to kilograms' => [MeasurementUnit::Grams, MeasurementUnit::Kilograms, 500, 0.5],
    'kilograms to grams' => [MeasurementUnit::Kilograms, MeasurementUnit::Grams, 1.5, 1500],
    'ounces to pounds' => [MeasurementUnit::Ounces, MeasurementUnit::Pounds, 16, 1],
    'pounds to ounces' => [MeasurementUnit::Pounds, MeasurementUnit::Ounces, 2, 32],
    'pounds to grams' => [MeasurementUnit::Pounds, MeasurementUnit::Grams, 1, 453.59237],
    'teaspoons to cups' => [MeasurementUnit::Teaspoons, MeasurementUnit::Cups, 48, 1.0],
    'tablespoons to teaspoons' => [MeasurementUnit::Tablespoons, MeasurementUnit::Teaspoons, 1, 3.0],
    'cups to milliliters' => [MeasurementUnit::Cups, MeasurementUnit::Milliliters, 1, 236.5882365],
    'milliliters to liters' => [MeasurementUnit::Milliliters, MeasurementUnit::Liters, 250, 0.25],
    'each to dozen' => [MeasurementUnit::Each, MeasurementUnit::Dozen, 18, 1.5],
    'dozen to each' => [MeasurementUnit::Dozen, MeasurementUnit::Each, 2, 24],
    'same unit' => [MeasurementUnit::Grams, MeasurementUnit::Grams, 7.25, 7.25],
]);

test('returns null when the units measure different dimensions', function (MeasurementUnit $from, MeasurementUnit $to) {
    expect($from->convert(1, $to))->toBeNull();
})->with([
    'volume to mass' => [MeasurementUnit::Cups, MeasurementUnit::Pounds],
    'mass to volume' => [MeasurementUnit::Grams, MeasurementUnit::Milliliters],
    'count to mass' => [MeasurementUnit::Each, MeasurementUnit::Grams],
    'mass to count' => [MeasurementUnit::Kilograms, MeasurementUnit::Dozen],
]);

test('options can be limited to one dimension', function () {
    expect(MeasurementUnit::options(UnitDimension::Mass))->toBe([
        'oz' => 'Ounces (oz)',
        'lbs' => 'Pounds (lbs)',
        'g' => 'Grams (g)',
        'kg' => 'Kilograms (kg)',
    ])->and(MeasurementUnit::options())->toHaveCount(11);
});

test('every dimension has a label', function (UnitDimension $dimension, string $label) {
    expect($dimension->getLabel())->toBe($label);
})->with([
    'mass' => [UnitDimension::Mass, 'Mass'],
    'volume' => [UnitDimension::Volume, 'Volume'],
    'count' => [UnitDimension::Count, 'Count'],
]);
