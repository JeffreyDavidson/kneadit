<?php

use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;
use App\Services\Financial\ProductCostResolver;

beforeEach(fn () => setUpTenantTest());

test('returns product cost when set', function () {
    $product = Product::factory()->create(['cost' => 4.25]);

    expect(resolve(ProductCostResolver::class)->resolve($product))->toBe(4.25);
});

test('falls back to recipe cost when product cost is null', function () {
    $product = Product::factory()->create(['cost' => null]);
    Recipe::factory()->for($product)->create(['cost' => 7.50]);

    expect(resolve(ProductCostResolver::class)->resolve($product))->toBe(7.50);
});

test('falls back to ingredients JSON sum when no explicit costs set', function () {
    $product = Product::factory()->create(['cost' => null]);
    Recipe::factory()->for($product)->create([
        'cost' => null,
        'ingredients' => [
            ['name' => 'Flour', 'quantity' => 2, 'unit' => 'cups', 'cost' => 0.50],
            ['name' => 'Sugar', 'quantity' => 1, 'unit' => 'cup', 'cost' => 0.75],
        ],
    ]);

    expect(resolve(ProductCostResolver::class)->resolve($product))->toBe(1.75);
});

test('returns zero when no recipe and no product cost', function () {
    $product = Product::factory()->create(['cost' => null]);

    expect(resolve(ProductCostResolver::class)->resolve($product))->toBe(0.0);
});
