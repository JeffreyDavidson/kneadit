<?php

use App\Models\Product;
use App\Models\Recipe;
use App\Services\Financial\RecipeCostService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
});

test('it calculates total recipe cost from ingredients', function () {
    $product = Product::factory()->create(['price' => 20.00]);
    $recipe = Recipe::factory()->create([
        'product_id' => $product->id,
        'ingredients' => [
            ['name' => 'Flour', 'quantity' => 2, 'unit' => 'cups', 'cost' => 0.50],
            ['name' => 'Sugar', 'quantity' => 1, 'unit' => 'cup', 'cost' => 0.75],
        ],
    ]);

    $cost = resolve(RecipeCostService::class)->calculateCosts($recipe);

    expect($cost)->toBe(1.75);
});

test('it calculates current margin from product price and recipe cost', function () {
    $product = Product::factory()->create(['price' => 20.00]);
    $recipe = Recipe::factory()->create([
        'product_id' => $product->id,
        'ingredients' => [],
    ]);
    $recipe->load('product');

    $margin = resolve(RecipeCostService::class)->calculateCurrentMargin($recipe, 5.00);

    expect($margin)->toBe(75.0);
});

test('it calculates suggested price from cost and target margin', function () {
    $price = resolve(RecipeCostService::class)->calculateSuggestedPrice(5.00, 60.0);

    expect($price)->toBe(12.5);
});
