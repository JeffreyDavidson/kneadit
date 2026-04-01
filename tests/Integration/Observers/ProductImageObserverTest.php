<?php

use App\Models\Inventory\Product;
use App\Models\Inventory\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('first image is set as primary on save', function () {
    $product = Product::factory()->create();
    $image = ProductImage::factory()->recycle($product)->create([
        'path' => 'products/test.jpg',
        'sort_order' => 1,
    ]);

    expect($image->fresh()->is_primary)->toBeTrue()
        ->and($product->fresh()->image)->toBe('products/test.jpg');
});

test('lowest sort order image becomes primary when new image added', function () {
    $product = Product::factory()->create();

    $first = ProductImage::factory()->recycle($product)->create([
        'path' => 'products/first.jpg',
        'sort_order' => 2,
    ]);

    $second = ProductImage::factory()->recycle($product)->create([
        'path' => 'products/second.jpg',
        'sort_order' => 1,
    ]);

    expect($second->fresh()->is_primary)->toBeTrue()
        ->and($first->fresh()->is_primary)->toBeFalse()
        ->and($product->fresh()->image)->toBe('products/second.jpg');
});
