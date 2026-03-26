<?php

use App\Actions\AdjustIngredientStock;
use App\Models\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['database.connections.central' => config('database.connections.sqlite')]);
    $tenantMigrationPath = database_path('migrations/tenant');
    if (is_dir($tenantMigrationPath)) {
        test()->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
    }
});

test('adjust stock creates stock adjustment record', function () {
    $ingredient = Ingredient::query()->create([
        'name' => 'Flour',
        'unit' => 'kg',
        'current_stock' => 50,
        'low_stock_threshold' => 10,
        'cost_per_unit' => 2.50,
    ]);

    app(AdjustIngredientStock::class)($ingredient, 10, 'purchase', 'Restocked');

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

    app(AdjustIngredientStock::class)($ingredient, -5, 'usage', 'Order usage');

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

    app(AdjustIngredientStock::class)($ingredient, -5, 'usage', 'Over-used');

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

    expect($ingredient->isLowStock())->toBeTrue();
    expect($ingredient->isOutOfStock())->toBeFalse();
    expect($ingredient->getStockStatus())->toBe('low');
});

test('out of stock detection', function () {
    $ingredient = Ingredient::query()->create([
        'name' => 'Vanilla',
        'unit' => 'ml',
        'current_stock' => 0,
        'low_stock_threshold' => 50,
        'cost_per_unit' => 0.10,
    ]);

    expect($ingredient->isOutOfStock())->toBeTrue();
    expect($ingredient->getStockStatus())->toBe('out');
});

test('good stock status', function () {
    $ingredient = Ingredient::query()->create([
        'name' => 'Milk',
        'unit' => 'liters',
        'current_stock' => 100,
        'low_stock_threshold' => 10,
        'cost_per_unit' => 1.00,
    ]);

    expect($ingredient->isLowStock())->toBeFalse();
    expect($ingredient->isOutOfStock())->toBeFalse();
    expect($ingredient->getStockStatus())->toBe('good');
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
