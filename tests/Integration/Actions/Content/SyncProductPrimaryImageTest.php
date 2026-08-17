<?php

use App\Actions\Content\SyncProductPrimaryImage;
use App\Models\Inventory\Product;
use App\Models\Inventory\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it marks the first image by sort order as primary', function () {
    $product = Product::factory()->create();

    ProductImage::factory()->for($product)->create(['sort_order' => 3, 'is_primary' => false]);
    ProductImage::factory()->for($product)->create(['sort_order' => 1, 'is_primary' => false]);
    ProductImage::factory()->for($product)->create(['sort_order' => 2, 'is_primary' => false]);

    ProductImage::query()->where('product_id', $product->id)->update(['is_primary' => false]);

    resolve(SyncProductPrimaryImage::class)($product->id);

    $images = ProductImage::query()
        ->where('product_id', $product->id)
        ->orderBy('sort_order')
        ->get();
    $first = $images->get(0);
    $second = $images->get(1);
    $third = $images->get(2);

    if (! $first instanceof ProductImage || ! $second instanceof ProductImage || ! $third instanceof ProductImage) {
        throw new RuntimeException('Expected three product images.');
    }

    expect($first->sort_order)->toBe(1)
        ->and($first->is_primary)->toBeTrue()
        ->and($second->is_primary)->toBeFalse()
        ->and($third->is_primary)->toBeFalse();
});

test('it promotes next image when primary is deleted', function () {
    $product = Product::factory()->create();

    $primary = ProductImage::factory()->for($product)->create(['sort_order' => 1, 'is_primary' => true]);
    ProductImage::factory()->for($product)->create(['sort_order' => 2, 'is_primary' => false]);

    $primary->delete();

    $remaining = ProductImage::query()
        ->where('product_id', $product->id)
        ->firstOrFail();

    expect($remaining->is_primary)->toBeTrue();
});

test('it syncs product image column with primary image path', function () {
    $product = Product::factory()->create(['image' => null]);

    $image1 = ProductImage::factory()->for($product)->create(['sort_order' => 1, 'path' => 'images/first.jpg']);
    ProductImage::factory()->for($product)->create(['sort_order' => 2, 'path' => 'images/second.jpg']);

    ProductImage::query()->where('product_id', $product->id)->update(['is_primary' => false]);

    resolve(SyncProductPrimaryImage::class)($product->id);

    expect($product->refresh()->image)->toBe('images/first.jpg');
});
