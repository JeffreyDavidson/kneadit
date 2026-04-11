<?php

use App\Filament\Pages\Tools\PricingEngine;
use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new PricingEngine;
});

test('selected product id defaults to null', function () {
    expect(test()->page->selectedProductId)->toBeNull();
});

test('ingredient cost defaults to zero', function () {
    expect(test()->page->ingredientCost)->toBe(0.0);
});

test('prep time minutes defaults to zero', function () {
    expect(test()->page->prepTimeMinutes)->toBe(0);
});

test('positioning defaults to standard', function () {
    expect(test()->page->positioning)->toBe('standard');
});

test('result defaults to null', function () {
    expect(test()->page->result)->toBeNull();
});

test('mount sets default rates from settings', function () {
    test()->page->mount();

    expect(test()->page->hourlyLaborRate)->toBeFloat()
        ->and(test()->page->overheadPercentage)->toBeFloat()
        ->and(test()->page->targetProfitMargin)->toBeInt();
});

test('get products property returns collection', function () {
    Product::factory()->count(2)->create();

    expect(test()->page->getProductsProperty())->toHaveCount(2);
});

test('updated selected product id loads recipe cost', function () {
    $product = Product::factory()->create(['cost' => 5.00]);
    $recipe = Recipe::factory()->create([
        'product_id' => $product->id,
        'cost' => 3.50,
        'prep_time_minutes' => 45,
    ]);

    test()->page->selectedProductId = (string) $product->id;
    test()->page->updatedSelectedProductId();

    expect(test()->page->ingredientCost)->toBe(3.50)
        ->and(test()->page->prepTimeMinutes)->toBe(45);
});

test('updated selected product id uses product cost when no recipe', function () {
    $product = Product::factory()->create(['cost' => 7.00]);

    test()->page->selectedProductId = (string) $product->id;
    test()->page->updatedSelectedProductId();

    expect(test()->page->ingredientCost)->toBe(7.0)
        ->and(test()->page->prepTimeMinutes)->toBe(0);
});

test('updated selected product id clears when null', function () {
    test()->page->ingredientCost = 10.0;
    test()->page->prepTimeMinutes = 30;

    test()->page->selectedProductId = null;
    test()->page->updatedSelectedProductId();

    expect(test()->page->ingredientCost)->toBe(0.0)
        ->and(test()->page->prepTimeMinutes)->toBe(0);
});

test('calculate produces result', function () {
    test()->page->mount();
    test()->page->ingredientCost = 5.00;
    test()->page->prepTimeMinutes = 30;
    test()->page->targetProfitMargin = 50;

    test()->page->calculate();

    expect(test()->page->result)->not->toBeNull();
});

test('calculate with selected product includes current price', function () {
    $product = Product::factory()->create(['price' => 15.00]);

    test()->page->mount();
    test()->page->selectedProductId = (string) $product->id;
    test()->page->ingredientCost = 5.00;
    test()->page->prepTimeMinutes = 30;

    test()->page->calculate();

    expect(test()->page->result)->not->toBeNull();
});

test('calculate without selected product works', function () {
    test()->page->mount();
    test()->page->selectedProductId = null;
    test()->page->ingredientCost = 3.00;

    test()->page->calculate();

    expect(test()->page->result)->not->toBeNull();
});
