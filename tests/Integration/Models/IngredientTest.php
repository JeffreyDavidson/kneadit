<?php

use App\Actions\Inventory\AdjustIngredientStock;
use App\Enums\StockStatus;
use App\Models\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
});

test('adjust stock creates stock adjustment record', function () {
    $ingredient = Ingredient::query()->create([
        'name' => 'Flour',
        'unit' => 'kg',
        'current_stock' => 50,
        'low_stock_threshold' => 10,
        'cost_per_unit' => 2.50,
    ]);

    resolve(AdjustIngredientStock::class)($ingredient, 10, 'purchase', 'Restocked');

    assertDatabaseHas('stock_adjustments', [
        'ingredient_id' => $ingredient->id,
        'quantity' => 10.00,
        'type' => 'purchase',
        'notes' => 'Restocked',
    ]);
});

test('adjust stock updates current stock', function () {
    $ingredient = Ingredient::query()->create([
        'name' => 'Sugar',
        'unit' => 'kg',
        'current_stock' => 20,
        'low_stock_threshold' => 5,
        'cost_per_unit' => 1.50,
    ]);

    resolve(AdjustIngredientStock::class)($ingredient, -5, 'usage', 'Order usage');

    expect($ingredient->fresh()->current_stock)->toBe('15.00');
});

test('stock can go below zero', function () {
    $ingredient = Ingredient::query()->create([
        'name' => 'Butter',
        'unit' => 'kg',
        'current_stock' => 2,
        'low_stock_threshold' => 5,
        'cost_per_unit' => 4.00,
    ]);

    resolve(AdjustIngredientStock::class)($ingredient, -5, 'usage', 'Over-used');

    expect((float) $ingredient->fresh()->current_stock)->toBe(-3.0);
});

test('low stock threshold detection', function () {
    $ingredient = Ingredient::query()->create([
        'name' => 'Eggs',
        'unit' => 'dozen',
        'current_stock' => 8,
        'low_stock_threshold' => 10,
        'cost_per_unit' => 3.00,
    ]);

    expect($ingredient->is_low_stock)->toBeTrue()->and($ingredient->is_out_of_stock)->toBeFalse()->and($ingredient->stock_status)->toBe(StockStatus::Low);
});

test('out of stock detection', function () {
    $ingredient = Ingredient::query()->create([
        'name' => 'Vanilla',
        'unit' => 'ml',
        'current_stock' => 0,
        'low_stock_threshold' => 50,
        'cost_per_unit' => 0.10,
    ]);

    expect($ingredient->is_out_of_stock)->toBeTrue()->and($ingredient->stock_status)->toBe(StockStatus::Out);
});

test('good stock status', function () {
    $ingredient = Ingredient::query()->create([
        'name' => 'Milk',
        'unit' => 'liters',
        'current_stock' => 100,
        'low_stock_threshold' => 10,
        'cost_per_unit' => 1.00,
    ]);

    expect($ingredient->is_low_stock)->toBeFalse()->and($ingredient->is_out_of_stock)->toBeFalse()->and($ingredient->stock_status)->toBe(StockStatus::Good);
});

test('cost per unit is stored correctly', function () {
    $ingredient = Ingredient::query()->create([
        'name' => 'Cocoa',
        'unit' => 'kg',
        'current_stock' => 10,
        'low_stock_threshold' => 2,
        'cost_per_unit' => 12.75,
    ]);

    expect((float) $ingredient->cost_per_unit)->toBe(12.75);
});
