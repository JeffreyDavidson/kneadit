<?php

use App\Builders\Inventory\IngredientQueryBuilder;
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

function ingredientQuery(): IngredientQueryBuilder
{
    return Ingredient::query();
}

beforeEach(fn () => setUpTenantTest());

test('lowStock returns ingredients at or below their threshold', function () {
    Ingredient::factory()->lowStock()->create();
    Ingredient::factory()->outOfStock()->create();
    Ingredient::factory()->create(['current_stock' => 10, 'low_stock_threshold' => 5]);

    $ingredients = ingredientQuery()->lowStock()->get();

    expect($ingredients)->toHaveCount(2);
});

test('outOfStock returns ingredients without remaining stock', function () {
    Ingredient::factory()->outOfStock()->create();
    Ingredient::factory()->lowStock()->create();

    $ingredients = ingredientQuery()->outOfStock()->get();

    expect($ingredients)->toHaveCount(1);
});

test('withActiveSuppliers excludes inactive suppliers from the loaded relationship', function () {
    $ingredient = Ingredient::factory()->create();
    $activeSupplier = Supplier::factory()->create(['is_active' => true]);
    $inactiveSupplier = Supplier::factory()->create(['is_active' => false]);
    $ingredient->suppliers()->attach([$activeSupplier->id, $inactiveSupplier->id]);

    $result = ingredientQuery()->withActiveSuppliers()->findOrFail($ingredient->id);

    expect($result->suppliers)->toHaveCount(1)
        ->and($result->suppliers->firstOrFail()->is($activeSupplier))->toBeTrue();
});
