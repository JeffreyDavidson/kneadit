<?php

use App\Models\Inventory\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('price is cast to Money value object', function () {
    $product = Product::factory()->create(['price' => 10.00]);

    expect($product->refresh()->price->dollars())->toBe(10.00);
});

test('cost can be null', function () {
    $product = Product::factory()->create(['price' => 10.00, 'cost' => null]);

    expect($product->refresh()->cost)->toBeNull();
});
