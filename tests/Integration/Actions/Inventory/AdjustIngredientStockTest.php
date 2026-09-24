<?php

use App\Actions\Inventory\AdjustIngredientStock;
use App\Enums\Inventory\StockAdjustmentType;
use App\Exceptions\Inventory\StockWouldGoNegativeException;
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\StockAdjustment;
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

test('rolls back the stock change when the adjustment record cannot be created', function () {
    $ingredient = Ingredient::factory()->create(['current_stock' => 5]);

    StockAdjustment::creating(function (StockAdjustment $adjustment): never {
        throw new RuntimeException("Could not create stock adjustment for ingredient {$adjustment->ingredient_id}.");
    });

    expect(fn () => resolve(AdjustIngredientStock::class)($ingredient, -2, StockAdjustmentType::Usage))
        ->toThrow(RuntimeException::class, 'Could not create stock adjustment')
        ->and($ingredient->fresh()->current_stock)->toBe('5.00');
    assertDatabaseMissing('stock_adjustments', ['ingredient_id' => $ingredient->id]);
});

test('checks the latest stock value before allowing a deduction', function () {
    $ingredient = Ingredient::factory()->create(['current_stock' => 5]);
    $staleIngredient = Ingredient::query()->findOrFail($ingredient->getKey());

    resolve(AdjustIngredientStock::class)($ingredient, -4, StockAdjustmentType::Usage);

    expect(fn () => resolve(AdjustIngredientStock::class)($staleIngredient, -2, StockAdjustmentType::Usage))
        ->toThrow(StockWouldGoNegativeException::class)
        ->and($ingredient->fresh()->current_stock)->toBe('1.00')
        ->and($ingredient->stockAdjustments()->count())->toBe(1);
});
