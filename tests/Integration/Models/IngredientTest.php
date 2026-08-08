<?php

use App\Actions\Inventory\AdjustIngredientStock;
use App\Enums\Inventory\StockAdjustmentType;
use App\Enums\Inventory\StockStatus;
use App\Exceptions\Inventory\StockWouldGoNegativeException;
use App\Models\Inventory\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseHas;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
});

test('adjust stock creates stock adjustment record', function () {
    $ingredient = Ingredient::factory()->create([
        'name' => 'Flour',
        'unit' => 'kg',
        'current_stock' => 50,
        'low_stock_threshold' => 10,
        'cost_per_unit' => 2.50,
    ]);

    resolve(AdjustIngredientStock::class)($ingredient, 10, StockAdjustmentType::Purchase, 'Restocked');

    assertDatabaseHas('stock_adjustments', [
        'ingredient_id' => $ingredient->id,
        'quantity' => 10.00,
        'type' => 'purchase',
        'notes' => 'Restocked',
    ]);
});

test('adjust stock updates current stock', function () {
    $ingredient = Ingredient::factory()->create([
        'name' => 'Sugar',
        'unit' => 'kg',
        'current_stock' => 20,
        'low_stock_threshold' => 5,
        'cost_per_unit' => 1.50,
    ]);

    resolve(AdjustIngredientStock::class)($ingredient, -5, StockAdjustmentType::Usage, 'Order usage');

    expect($ingredient->fresh()->current_stock)->toBe('15.00');
});

test('AdjustIngredientStock throws when an adjustment would push stock below zero', function () {
    $ingredient = Ingredient::factory()->create([
        'name' => 'Butter',
        'unit' => 'kg',
        'current_stock' => 2,
        'low_stock_threshold' => 5,
        'cost_per_unit' => 4.00,
    ]);

    expect(fn () => resolve(AdjustIngredientStock::class)($ingredient, -5, StockAdjustmentType::Usage, 'Over-used'))
        ->toThrow(StockWouldGoNegativeException::class);

    // Stock unchanged; no adjustment row written.
    expect($ingredient->fresh()->current_stock)->toBe('2.00');
});

test('low stock threshold detection', function () {
    $ingredient = Ingredient::factory()->create([
        'name' => 'Eggs',
        'unit' => 'dozen',
        'current_stock' => 8,
        'low_stock_threshold' => 10,
        'cost_per_unit' => 3.00,
    ]);

    expect(StockStatus::resolve($ingredient))->toBe(StockStatus::Low);
});

test('out of stock detection', function () {
    $ingredient = Ingredient::factory()->create([
        'name' => 'Vanilla',
        'unit' => 'ml',
        'current_stock' => 0,
        'low_stock_threshold' => 50,
        'cost_per_unit' => 0.10,
    ]);

    expect(StockStatus::resolve($ingredient))->toBe(StockStatus::Out);
});

test('good stock status', function () {
    $ingredient = Ingredient::factory()->create([
        'name' => 'Milk',
        'unit' => 'liters',
        'current_stock' => 100,
        'low_stock_threshold' => 10,
        'cost_per_unit' => 1.00,
    ]);

    expect(StockStatus::resolve($ingredient))->toBe(StockStatus::Good);
});

test('cost per unit is stored correctly', function () {
    $ingredient = Ingredient::factory()->create([
        'name' => 'Cocoa',
        'unit' => 'kg',
        'current_stock' => 10,
        'low_stock_threshold' => 2,
        'cost_per_unit' => 12.75,
    ]);

    expect($ingredient->cost_per_unit->dollars())->toBe(12.75);
});
