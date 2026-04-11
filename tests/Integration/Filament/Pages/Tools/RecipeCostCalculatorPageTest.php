<?php

use App\Filament\Pages\Tools\RecipeCostCalculator;
use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new RecipeCostCalculator;
});

test('selected recipe id defaults to null', function () {
    expect(test()->page->selectedRecipeId)->toBeNull();
});

test('selected recipe defaults to null', function () {
    expect(test()->page->selectedRecipe)->toBeNull();
});

test('target margin percentage defaults to 65', function () {
    expect(test()->page->targetMarginPercentage)->toBe(65.0);
});

test('analysis defaults to null', function () {
    expect(test()->page->analysis)->toBeNull();
});

test('mount loads all recipes', function () {
    Recipe::factory()->count(3)->create();

    test()->page->mount();

    expect(test()->page->recipes)->toHaveCount(3);
});

test('updated selected recipe id loads recipe and refreshes analysis', function () {
    $product = Product::factory()->create(['price' => 15.00]);
    $recipe = Recipe::factory()->create(['product_id' => $product->id, 'cost' => 4.00]);

    test()->page->selectedRecipeId = $recipe->id;
    test()->page->updatedSelectedRecipeId();

    expect(test()->page->selectedRecipe)->not->toBeNull()
        ->and(test()->page->selectedRecipe->id)->toBe($recipe->id)
        ->and(test()->page->analysis)->not->toBeNull();
});

test('updated selected recipe id clears when null', function () {
    test()->page->selectedRecipeId = null;
    test()->page->updatedSelectedRecipeId();

    expect(test()->page->selectedRecipe)->toBeNull()
        ->and(test()->page->analysis)->toBeNull();
});

test('refresh analysis returns null when no recipe product', function () {
    test()->page->selectedRecipe = null;
    test()->page->refreshAnalysis();

    expect(test()->page->analysis)->toBeNull();
});

test('refresh analysis sets analysis for valid recipe', function () {
    $product = Product::factory()->create(['price' => 12.00]);
    $recipe = Recipe::factory()->create(['product_id' => $product->id, 'cost' => 3.00]);

    test()->page->selectedRecipe = $recipe->load('product');
    test()->page->refreshAnalysis();

    expect(test()->page->analysis)->not->toBeNull();
});

test('updated target margin percentage refreshes analysis', function () {
    $product = Product::factory()->create(['price' => 10.00]);
    $recipe = Recipe::factory()->create(['product_id' => $product->id, 'cost' => 3.00]);

    test()->page->selectedRecipe = $recipe->load('product');
    test()->page->targetMarginPercentage = 50.0;
    test()->page->updatedTargetMarginPercentage();

    expect(test()->page->analysis)->not->toBeNull();
});
