<?php

use App\Actions\Inventory\AdjustIngredientStock;
use App\Models\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
});

test('it adjusts stock and creates adjustment record', function () {
    $ingredient = Ingredient::factory()->create(['current_stock' => 50]);

    app(AdjustIngredientStock::class)($ingredient, 10, 'purchase', 'Restocked');

    assertDatabaseHas('stock_adjustments', [
        'ingredient_id' => $ingredient->id,
        'quantity' => 10.00,
        'type' => 'purchase',
        'notes' => 'Restocked',
    ]);
    expect($ingredient->fresh()->current_stock)->toBe('60.00');
});
