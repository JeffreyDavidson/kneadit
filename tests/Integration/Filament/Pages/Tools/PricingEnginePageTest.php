<?php

use App\Filament\Pages\Tools\PricingEngine;
use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new PricingEngine;
});

test('selected product id defaults to null', function () {
    expect(testFixture('page', PricingEngine::class)->selectedProductId)->toBeNull();
});

test('ingredient cost defaults to zero', function () {
    expect(testFixture('page', PricingEngine::class)->ingredientCost)->toBe(0.0);
});

test('prep time minutes defaults to zero', function () {
    expect(testFixture('page', PricingEngine::class)->prepTimeMinutes)->toBe(0);
});

test('positioning defaults to standard', function () {
    expect(testFixture('page', PricingEngine::class)->positioning)->toBe('standard');
});

test('result defaults to null', function () {
    expect(testFixture('page', PricingEngine::class)->result)->toBeNull();
});

test('mount sets default rates from settings', function () {
    testFixture('page', PricingEngine::class)->mount();

    expect(testFixture('page', PricingEngine::class)->hourlyLaborRate)->toBeFloat()
        ->and(testFixture('page', PricingEngine::class)->overheadPercentage)->toBeFloat()
        ->and(testFixture('page', PricingEngine::class)->targetProfitMargin)->toBeInt();
});

test('get products property returns collection', function () {
    Product::factory()->count(2)->create();

    expect(testFixture('page', PricingEngine::class)->getProductsProperty())->toHaveCount(2);
});

test('updated selected product id loads recipe cost', function () {
    $product = Product::factory()->create(['cost' => 5.00]);
    $recipe = Recipe::factory()->create([
        'product_id' => $product->id,
        'cost' => 3.50,
        'prep_time_minutes' => 45,
    ]);

    testFixture('page', PricingEngine::class)->selectedProductId = (string) $product->id;
    testFixture('page', PricingEngine::class)->updatedSelectedProductId();

    expect(testFixture('page', PricingEngine::class)->ingredientCost)->toBe(3.50)
        ->and(testFixture('page', PricingEngine::class)->prepTimeMinutes)->toBe(45);
});

test('updated selected product id uses product cost when no recipe', function () {
    $product = Product::factory()->create(['cost' => 7.00]);

    testFixture('page', PricingEngine::class)->selectedProductId = (string) $product->id;
    testFixture('page', PricingEngine::class)->updatedSelectedProductId();

    expect(testFixture('page', PricingEngine::class)->ingredientCost)->toBe(7.0)
        ->and(testFixture('page', PricingEngine::class)->prepTimeMinutes)->toBe(0);
});

test('updated selected product id clears when null', function () {
    testFixture('page', PricingEngine::class)->ingredientCost = 10.0;
    testFixture('page', PricingEngine::class)->prepTimeMinutes = 30;

    testFixture('page', PricingEngine::class)->selectedProductId = null;
    testFixture('page', PricingEngine::class)->updatedSelectedProductId();

    expect(testFixture('page', PricingEngine::class)->ingredientCost)->toBe(0.0)
        ->and(testFixture('page', PricingEngine::class)->prepTimeMinutes)->toBe(0);
});

test('calculate produces result', function () {
    testFixture('page', PricingEngine::class)->mount();
    testFixture('page', PricingEngine::class)->ingredientCost = 5.00;
    testFixture('page', PricingEngine::class)->prepTimeMinutes = 30;
    testFixture('page', PricingEngine::class)->targetProfitMargin = 50;

    testFixture('page', PricingEngine::class)->calculate();

    expect(testFixture('page', PricingEngine::class)->result)->not->toBeNull();
});

test('calculate with selected product includes current price', function () {
    $product = Product::factory()->create(['price' => 15.00]);

    testFixture('page', PricingEngine::class)->mount();
    testFixture('page', PricingEngine::class)->selectedProductId = (string) $product->id;
    testFixture('page', PricingEngine::class)->ingredientCost = 5.00;
    testFixture('page', PricingEngine::class)->prepTimeMinutes = 30;

    testFixture('page', PricingEngine::class)->calculate();

    expect(testFixture('page', PricingEngine::class)->result)->not->toBeNull();
});

test('calculate without selected product works', function () {
    testFixture('page', PricingEngine::class)->mount();
    testFixture('page', PricingEngine::class)->selectedProductId = null;
    testFixture('page', PricingEngine::class)->ingredientCost = 3.00;

    testFixture('page', PricingEngine::class)->calculate();

    expect(testFixture('page', PricingEngine::class)->result)->not->toBeNull();
});
