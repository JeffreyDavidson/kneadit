<?php

use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('lowStock returns ingredients at or below their threshold', function () {
    Ingredient::factory()->lowStock()->create();
    Ingredient::factory()->outOfStock()->create();
    Ingredient::factory()->create(['current_stock' => 10, 'low_stock_threshold' => 5]);

    $ingredients = Ingredient::query()->lowStock()->get();

    expect($ingredients)->toHaveCount(2);
});

test('outOfStock returns ingredients without remaining stock', function () {
    Ingredient::factory()->outOfStock()->create();
    Ingredient::factory()->lowStock()->create();

    $ingredients = Ingredient::query()->outOfStock()->get();

    expect($ingredients)->toHaveCount(1);
});

test('withActiveSuppliers excludes inactive suppliers from the loaded relationship', function () {
    $ingredient = Ingredient::factory()->create();
    $activeSupplier = Supplier::factory()->create(['is_active' => true]);
    $inactiveSupplier = Supplier::factory()->create(['is_active' => false]);
    $ingredient->suppliers()->attach([$activeSupplier->id, $inactiveSupplier->id]);

    $result = Ingredient::query()->withActiveSuppliers()->findOrFail($ingredient->id);

    expect($result->suppliers)->toHaveCount(1)
        ->and($result->suppliers->first()?->is($activeSupplier))->toBeTrue();
});
