<?php

use App\Enums\StockStatus;
use App\Models\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('stock status returns Good when above threshold', function () {
    $ingredient = Ingredient::factory()->create([
        'current_stock' => 100,
        'low_stock_threshold' => 10,
    ]);

    expect($ingredient->getStockStatus())->toBe(StockStatus::Good);
});

test('stock status returns Low when at or below threshold', function () {
    $ingredient = Ingredient::factory()->create([
        'current_stock' => 5,
        'low_stock_threshold' => 10,
    ]);

    expect($ingredient->getStockStatus())->toBe(StockStatus::Low);
});

test('stock status returns Out when stock is zero', function () {
    $ingredient = Ingredient::factory()->create([
        'current_stock' => 0,
        'low_stock_threshold' => 10,
    ]);

    expect($ingredient->getStockStatus())->toBe(StockStatus::Out);
});
