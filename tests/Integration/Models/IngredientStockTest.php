<?php

use App\Enums\Inventory\StockStatus;
use App\Models\Inventory\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('stock status returns Good when above threshold', function () {
    $ingredient = Ingredient::factory()->create([
        'current_stock' => 100,
        'low_stock_threshold' => 10,
    ]);

    expect($ingredient->stock_status)->toBe(StockStatus::Good);
});

test('stock status returns Low when at or below threshold', function () {
    $ingredient = Ingredient::factory()->create([
        'current_stock' => 5,
        'low_stock_threshold' => 10,
    ]);

    expect($ingredient->stock_status)->toBe(StockStatus::Low);
});

test('stock status returns Out when stock is zero', function () {
    $ingredient = Ingredient::factory()->create([
        'current_stock' => 0,
        'low_stock_threshold' => 10,
    ]);

    expect($ingredient->stock_status)->toBe(StockStatus::Out);
});

test('isLowStock and isOutOfStock return correct booleans', function () {
    $normal = Ingredient::factory()->create(['current_stock' => 50, 'low_stock_threshold' => 10]);
    $low = Ingredient::factory()->lowStock()->create();
    $out = Ingredient::factory()->outOfStock()->create();

    expect($normal->is_low_stock)->toBeFalse()
        ->and($normal->is_out_of_stock)->toBeFalse()
        ->and($low->is_low_stock)->toBeTrue()
        ->and($low->is_out_of_stock)->toBeFalse()
        ->and($out->is_out_of_stock)->toBeTrue();
});
