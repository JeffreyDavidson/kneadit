<?php

use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;
use App\Presenters\RecipePresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('profitMargin returns the percentage for a priced product and a costed recipe', function () {
    $product = Product::factory()->create(['price' => 10.00]);
    $recipe = Recipe::factory()->for($product)->create(['cost' => 4.00]);

    // ProfitMargin::calculate divides by servings (default 1 here) before comparing to price.
    expect(RecipePresenter::for($recipe)->profitMargin())->toBe(60.0);
});

test('profitMargin returns null when the recipe has no cost', function () {
    $product = Product::factory()->create(['price' => 10.00]);
    $recipe = Recipe::factory()->for($product)->create(['cost' => null]);

    expect(RecipePresenter::for($recipe)->profitMargin())->toBeNull();
});

test('profitMargin returns null when the recipe has no product', function () {
    $recipe = Recipe::factory()->withoutProduct()->withCost(4.00)->create();

    expect(RecipePresenter::for($recipe)->profitMargin())->toBeNull();
});
