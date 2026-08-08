<?php

use App\Models\Inventory\Product;
use App\Models\Inventory\ProductImage;
use App\Models\Inventory\SeasonalItem;
use App\Presenters\ProductPresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('primaryImageUrl returns the primary image path', function () {
    $product = Product::factory()->create(['image' => null]);
    ProductImage::factory()->primary()->for($product)->create(['path' => 'products/primary.jpg']);

    $url = ProductPresenter::for($product->fresh())->primaryImageUrl();

    expect($url)->toBeString()->and($url)->toContain('primary.jpg');
});

test('primaryImageUrl falls back to the image column when no primary image exists', function () {
    $product = Product::factory()->create(['image' => 'products/fallback.jpg']);

    $url = ProductPresenter::for($product)->primaryImageUrl();

    expect($url)->toBeString()->and($url)->toContain('fallback.jpg');
});

test('primaryImageUrl returns null when neither a primary image nor image column is set', function () {
    $product = Product::factory()->create(['image' => null]);

    expect(ProductPresenter::for($product)->primaryImageUrl())->toBeNull();
});

test('isInSeason returns true for a product with a currently-available seasonal entry', function () {
    $product = Product::factory()->create();
    SeasonalItem::factory()->for($product)->current()->create();

    expect(ProductPresenter::for($product)->isInSeason())->toBeTrue();
});

test('isInSeason returns false for a product whose only seasonal entry is expired', function () {
    $product = Product::factory()->create();
    SeasonalItem::factory()->for($product)->expired()->create();

    expect(ProductPresenter::for($product)->isInSeason())->toBeFalse();
});

test('isInSeason returns true when the product has no seasonal entries', function () {
    $product = Product::factory()->create();

    expect(ProductPresenter::for($product)->isInSeason())->toBeTrue();
});

test('seasonalBadge returns Limited Time for a current seasonal entry', function () {
    $product = Product::factory()->create();
    SeasonalItem::factory()->for($product)->current()->create();

    expect(ProductPresenter::for($product)->seasonalBadge())->toBe('Limited Time');
});

test('seasonalBadge returns an availability range for an upcoming seasonal entry', function () {
    $product = Product::factory()->create();
    SeasonalItem::factory()->for($product)->upcoming()->create();

    expect(ProductPresenter::for($product)->seasonalBadge())->toContain('Available');
});

test('seasonalBadge returns null when the product has no seasonal entries', function () {
    $product = Product::factory()->create();

    expect(ProductPresenter::for($product)->seasonalBadge())->toBeNull();
});
