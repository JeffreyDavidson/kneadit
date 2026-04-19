<?php

use App\Filament\Pages\Tools\PriceSuggestionTool;
use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new PriceSuggestionTool;
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

test('mount loads recipes with cost', function () {
    Recipe::factory()->create(['cost' => 5.00]);
    Recipe::factory()->create(['cost' => null]);
    Recipe::factory()->create(['cost' => 0]);

    test()->page->mount();

    expect(test()->page->recipes)->toHaveCount(1);
});

test('mount generates margin comparisons', function () {
    test()->page->mount();

    expect(test()->page->marginComparisons)->toBeInstanceOf(Illuminate\Support\Collection::class);
});

test('updated selected recipe id loads recipe', function () {
    $product = Product::factory()->create(['price' => 10.00]);
    $recipe = Recipe::factory()->for($product)->withCost(3.00)->create();

    test()->page->selectedRecipeId = $recipe->id;
    test()->page->updatedSelectedRecipeId();

    expect(test()->page->selectedRecipe)->not->toBeNull()
        ->and(test()->page->selectedRecipe->id)->toBe($recipe->id);
});

test('updated selected recipe id clears when null', function () {
    test()->page->selectedRecipeId = null;
    test()->page->updatedSelectedRecipeId();

    expect(test()->page->selectedRecipe)->toBeNull();
});

test('generate margin comparisons empty when no recipe selected', function () {
    test()->page->selectedRecipe = null;
    test()->page->generateMarginComparisons();

    expect(test()->page->marginComparisons)->toBeEmpty();
});

test('generate margin comparisons with recipe', function () {
    $product = Product::factory()->create(['price' => 10.00]);
    $recipe = Recipe::factory()->for($product)->withCost(3.00)->create();

    test()->page->selectedRecipe = $recipe;
    test()->page->generateMarginComparisons();

    expect(test()->page->marginComparisons)->toHaveCount(4);
    expect(test()->page->marginComparisons->first())->toHaveKeys(['margin', 'price', 'difference', 'difference_percentage', 'is_target']);
});

test('get suggested price returns zero when no recipe', function () {
    expect(test()->page->getSuggestedPrice())->toBe(0.0);
});

test('get suggested price returns value for recipe with cost', function () {
    $product = Product::factory()->create(['price' => 10.00]);
    $recipe = Recipe::factory()->for($product)->withCost(3.00)->create();

    test()->page->selectedRecipe = $recipe;

    expect(test()->page->getSuggestedPrice())->toBeGreaterThan(0);
});

test('get current margin returns null when no recipe product', function () {
    expect(test()->page->getCurrentMargin())->toBeNull();
});

test('get margin at current price returns null when no recipe product', function () {
    expect(test()->page->getMarginAtCurrentPrice())->toBeNull();
});

test('get price difference returns null when no recipe product', function () {
    expect(test()->page->getPriceDifference())->toBeNull();
});

test('get price difference returns data for valid recipe', function () {
    $product = Product::factory()->create(['price' => 10.00]);
    $recipe = Recipe::factory()->for($product)->withCost(3.00)->create();

    test()->page->selectedRecipe = $recipe->load('product');

    $diff = test()->page->getPriceDifference();

    expect($diff)->toHaveKeys(['amount', 'percentage', 'direction']);
});
