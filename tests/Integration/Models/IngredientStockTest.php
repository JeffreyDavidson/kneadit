<?php

use App\Enums\Inventory\StockStatus;
use App\Models\Inventory\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('StockStatus::resolve returns Good when above threshold', function () {
    $ingredient = Ingredient::factory()->create([
        'current_stock' => 100,
        'low_stock_threshold' => 10,
    ]);

    expect(StockStatus::resolve($ingredient))->toBe(StockStatus::Good);
});

test('StockStatus::resolve returns Low when at or below threshold', function () {
    $ingredient = Ingredient::factory()->create([
        'current_stock' => 5,
        'low_stock_threshold' => 10,
    ]);

    expect(StockStatus::resolve($ingredient))->toBe(StockStatus::Low);
});

test('StockStatus::resolve returns Out when stock is zero', function () {
    $ingredient = Ingredient::factory()->create([
        'current_stock' => 0,
        'low_stock_threshold' => 10,
    ]);

    expect(StockStatus::resolve($ingredient))->toBe(StockStatus::Out);
});

test('StockStatus::resolve handles factory states', function () {
    $normal = Ingredient::factory()->create(['current_stock' => 50, 'low_stock_threshold' => 10]);
    $low = Ingredient::factory()->lowStock()->create();
    $out = Ingredient::factory()->outOfStock()->create();

    expect(StockStatus::resolve($normal))->toBe(StockStatus::Good)
        ->and(StockStatus::resolve($low))->toBe(StockStatus::Low)
        ->and(StockStatus::resolve($out))->toBe(StockStatus::Out);
});
