<?php

use App\Filament\Pages\Tools\PriceSuggestionTool;
use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new PriceSuggestionTool;
});

test('selected recipe id defaults to null', function () {
    expect(testFixture('page', PriceSuggestionTool::class)->selectedRecipeId)->toBeNull();
});

test('selected recipe defaults to null', function () {
    expect(testFixture('page', PriceSuggestionTool::class)->selectedRecipe)->toBeNull();
});

test('target margin percentage defaults to 65', function () {
    expect(testFixture('page', PriceSuggestionTool::class)->targetMarginPercentage)->toBe(65.0);
});

test('mount loads recipes with cost', function () {
    Recipe::factory()->create(['cost' => 5.00]);
    Recipe::factory()->create(['cost' => null]);
    Recipe::factory()->create(['cost' => 0]);

    testFixture('page', PriceSuggestionTool::class)->mount();

    expect(testFixture('page', PriceSuggestionTool::class)->recipes)->toHaveCount(1);
});

test('mount generates margin comparisons', function () {
    testFixture('page', PriceSuggestionTool::class)->mount();

    expect(testFixture('page', PriceSuggestionTool::class)->marginComparisons)->toBeInstanceOf(Illuminate\Support\Collection::class);
});

test('updated selected recipe id loads recipe', function () {
    $product = Product::factory()->create(['price' => 10.00]);
    $recipe = Recipe::factory()->for($product)->withCost(3.00)->create();

    testFixture('page', PriceSuggestionTool::class)->selectedRecipeId = $recipe->id;
    testFixture('page', PriceSuggestionTool::class)->updatedSelectedRecipeId();

    expect(testFixture('page', PriceSuggestionTool::class)->selectedRecipe)->not->toBeNull()
        ->and(testFixture('page', PriceSuggestionTool::class)->selectedRecipe->id)->toBe($recipe->id);
});

test('updated selected recipe id clears when null', function () {
    testFixture('page', PriceSuggestionTool::class)->selectedRecipeId = null;
    testFixture('page', PriceSuggestionTool::class)->updatedSelectedRecipeId();

    expect(testFixture('page', PriceSuggestionTool::class)->selectedRecipe)->toBeNull();
});

test('generate margin comparisons empty when no recipe selected', function () {
    testFixture('page', PriceSuggestionTool::class)->selectedRecipe = null;
    testFixture('page', PriceSuggestionTool::class)->generateMarginComparisons();

    expect(testFixture('page', PriceSuggestionTool::class)->marginComparisons)->toBeEmpty();
});

test('generate margin comparisons with recipe', function () {
    $product = Product::factory()->create(['price' => 10.00]);
    $recipe = Recipe::factory()->for($product)->withCost(3.00)->create();

    testFixture('page', PriceSuggestionTool::class)->selectedRecipe = $recipe;
    testFixture('page', PriceSuggestionTool::class)->generateMarginComparisons();

    expect(testFixture('page', PriceSuggestionTool::class)->marginComparisons)->toHaveCount(4)
        ->and(testFixture('page', PriceSuggestionTool::class)->marginComparisons->firstOrFail())->toHaveKeys(['margin', 'price', 'difference', 'difference_percentage', 'is_target']);
});

test('get suggested price returns zero when no recipe', function () {
    expect(testFixture('page', PriceSuggestionTool::class)->getSuggestedPrice())->toBe(0.0);
});

test('get suggested price returns value for recipe with cost', function () {
    $product = Product::factory()->create(['price' => 10.00]);
    $recipe = Recipe::factory()->for($product)->withCost(3.00)->create();

    testFixture('page', PriceSuggestionTool::class)->selectedRecipe = $recipe;

    expect(testFixture('page', PriceSuggestionTool::class)->getSuggestedPrice())->toBeGreaterThan(0);
});

test('get current margin returns null when no recipe product', function () {
    expect(testFixture('page', PriceSuggestionTool::class)->getCurrentMargin())->toBeNull();
});

test('get margin at current price returns null when no recipe product', function () {
    expect(testFixture('page', PriceSuggestionTool::class)->getMarginAtCurrentPrice())->toBeNull();
});

test('get price difference returns null when no recipe product', function () {
    expect(testFixture('page', PriceSuggestionTool::class)->getPriceDifference())->toBeNull();
});

test('get price difference returns data for valid recipe', function () {
    $product = Product::factory()->create(['price' => 10.00]);
    $recipe = Recipe::factory()->for($product)->withCost(3.00)->create();

    testFixture('page', PriceSuggestionTool::class)->selectedRecipe = $recipe->load('product');

    $diff = testFixture('page', PriceSuggestionTool::class)->getPriceDifference();

    expect($diff)->toHaveKeys(['amount', 'percentage', 'direction']);
});
