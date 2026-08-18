<?php

use App\Models\Inventory\Product;
use App\Models\Inventory\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

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

test('syncs primary when only image is deleted', function () {
    $product = Product::factory()->create();

    $image = ProductImage::factory()->recycle($product)->create([
        'path' => 'products/only.jpg',
        'sort_order' => 1,
    ]);

    // Confirm it's primary
    expect($image->fresh()->is_primary)->toBeTrue();

    // Now add a second image, then delete the first
    $second = ProductImage::factory()->recycle($product)->create([
        'path' => 'products/second.jpg',
        'sort_order' => 2,
    ]);

    $image->delete();

    expect($second->fresh()->is_primary)->toBeTrue()
        ->and($product->fresh()->image)->toBe('products/second.jpg');
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
