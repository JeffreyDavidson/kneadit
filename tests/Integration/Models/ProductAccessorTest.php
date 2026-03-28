<?php

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('margin accessor calculates profit percentage', function () {
    $product = Product::factory()->create(['price' => 10.00, 'cost' => 4.00]);

    expect($product->margin)->toBe(60.0);
});

test('margin accessor returns null when no cost', function () {
    $product = Product::factory()->create(['price' => 10.00, 'cost' => null]);

    expect($product->margin)->toBeNull();
});
