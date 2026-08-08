<?php

use App\Actions\Inventory\AdjustIngredientStock;
use App\Enums\Inventory\StockAdjustmentType;
use App\Exceptions\Inventory\StockWouldGoNegativeException;
use App\Models\Inventory\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
});

test('it adjusts stock and creates adjustment record', function () {
    $ingredient = Ingredient::factory()->create(['current_stock' => 50]);

    resolve(AdjustIngredientStock::class)($ingredient, 10, StockAdjustmentType::Purchase, 'Restocked');

    assertDatabaseHas('stock_adjustments', [
        'ingredient_id' => $ingredient->id,
        'quantity' => 10.00,
        'type' => 'purchase',
        'notes' => 'Restocked',
    ]);
    expect($ingredient->fresh()->current_stock)->toBe('60.00');
});

test('throws when an adjustment would push stock below zero', function () {
    $ingredient = Ingredient::factory()->create(['current_stock' => 5]);

    expect(fn () => resolve(AdjustIngredientStock::class)($ingredient, -10, StockAdjustmentType::Usage))
        ->toThrow(StockWouldGoNegativeException::class);

    // Stock unchanged, no audit row written.
    expect($ingredient->fresh()->current_stock)->toBe('5.00');
    assertDatabaseMissing('stock_adjustments', ['ingredient_id' => $ingredient->id]);
});

test('allows adjustments that bring stock exactly to zero', function () {
    $ingredient = Ingredient::factory()->create(['current_stock' => 5]);

    resolve(AdjustIngredientStock::class)($ingredient, -5, StockAdjustmentType::Usage);

    expect($ingredient->fresh()->current_stock)->toBe('0.00');
});
