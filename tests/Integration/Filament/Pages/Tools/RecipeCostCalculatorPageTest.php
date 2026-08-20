<?php

use App\Filament\Pages\Tools\RecipeCostCalculator;
use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new RecipeCostCalculator;
});

test('selected recipe id defaults to null', function () {
    expect(testFixture('page', RecipeCostCalculator::class)->selectedRecipeId)->toBeNull();
});

test('selected recipe defaults to null', function () {
    expect(testFixture('page', RecipeCostCalculator::class)->selectedRecipe)->toBeNull();
});

test('target margin percentage defaults to 65', function () {
    expect(testFixture('page', RecipeCostCalculator::class)->targetMarginPercentage)->toBe(65.0);
});

test('analysis defaults to null', function () {
    expect(testFixture('page', RecipeCostCalculator::class)->analysis)->toBeNull();
});

test('mount loads all recipes', function () {
    Recipe::factory()->count(3)->create();

    testFixture('page', RecipeCostCalculator::class)->mount();

    expect(testFixture('page', RecipeCostCalculator::class)->recipes)->toHaveCount(3);
});

test('updated selected recipe id loads recipe and refreshes analysis', function () {
    $product = Product::factory()->create(['price' => 15.00]);
    $recipe = Recipe::factory()->for($product)->withCost(4.00)->create();

    testFixture('page', RecipeCostCalculator::class)->selectedRecipeId = $recipe->id;
    testFixture('page', RecipeCostCalculator::class)->updatedSelectedRecipeId();

    expect(testFixture('page', RecipeCostCalculator::class)->selectedRecipe)->not->toBeNull()
        ->and(testFixture('page', RecipeCostCalculator::class)->selectedRecipe->id)->toBe($recipe->id)
        ->and(testFixture('page', RecipeCostCalculator::class)->analysis)->not->toBeNull();
});

test('updated selected recipe id clears when null', function () {
    testFixture('page', RecipeCostCalculator::class)->selectedRecipeId = null;
    testFixture('page', RecipeCostCalculator::class)->updatedSelectedRecipeId();

    expect(testFixture('page', RecipeCostCalculator::class)->selectedRecipe)->toBeNull()
        ->and(testFixture('page', RecipeCostCalculator::class)->analysis)->toBeNull();
});

test('refresh analysis returns null when no recipe product', function () {
    testFixture('page', RecipeCostCalculator::class)->selectedRecipe = null;
    testFixture('page', RecipeCostCalculator::class)->refreshAnalysis();

    expect(testFixture('page', RecipeCostCalculator::class)->analysis)->toBeNull();
});

test('refresh analysis sets analysis for valid recipe', function () {
    $product = Product::factory()->create(['price' => 12.00]);
    $recipe = Recipe::factory()->for($product)->withCost(3.00)->create();

    testFixture('page', RecipeCostCalculator::class)->selectedRecipe = $recipe->load('product');
    testFixture('page', RecipeCostCalculator::class)->refreshAnalysis();

    expect(testFixture('page', RecipeCostCalculator::class)->analysis)->not->toBeNull();
});

test('updated target margin percentage refreshes analysis', function () {
    $product = Product::factory()->create(['price' => 10.00]);
    $recipe = Recipe::factory()->for($product)->withCost(3.00)->create();

    testFixture('page', RecipeCostCalculator::class)->selectedRecipe = $recipe->load('product');
    testFixture('page', RecipeCostCalculator::class)->targetMarginPercentage = 50.0;
    testFixture('page', RecipeCostCalculator::class)->updatedTargetMarginPercentage();

    expect(testFixture('page', RecipeCostCalculator::class)->analysis)->not->toBeNull();
});
