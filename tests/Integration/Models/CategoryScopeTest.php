<?php

use App\Models\Inventory\Category;
use App\Models\Inventory\Product;
use App\Models\Inventory\ProductImage;
use App\Models\Inventory\SeasonalItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('active scope returns only active categories', function () {
    $active = Category::factory()->create();
    Category::factory()->inactive()->create();

    $results = Category::query()->active()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($active->id);
});

test('withFeaturedProducts scope eager loads only active featured products', function () {
    $category = Category::factory()->active()->create();
    Product::factory()->recycle($category)->active()->featured()->create(['name' => 'Featured']);
    Product::factory()->recycle($category)->active()->create(['name' => 'Regular']);
    Product::factory()->recycle($category)->inactive()->featured()->create(['name' => 'Inactive']);

    $result = Category::query()->withFeaturedProducts()->first();

    expect($result->products)->toHaveCount(1)
        ->and($result->products->first()->name)->toBe('Featured');
});

test('withFeaturedProducts eager-loads seasonalItems and primaryImage to prevent N+1 in ProductPresenter', function () {
    $category = Category::factory()->active()->create();
    $product = Product::factory()->recycle($category)->active()->featured()->create();
    SeasonalItem::factory()->recycle($product)->create();
    ProductImage::factory()->recycle($product)->create(['is_primary' => true]);

    $loaded = Category::query()->withFeaturedProducts()->first()->products->first();

    expect($loaded->relationLoaded('seasonalItems'))->toBeTrue()
        ->and($loaded->relationLoaded('primaryImage'))->toBeTrue();
});

test('withActiveProducts eager-loads seasonalItems and primaryImage to prevent N+1 in ProductPresenter', function () {
    $category = Category::factory()->active()->create();
    $product = Product::factory()->recycle($category)->active()->create();
    SeasonalItem::factory()->recycle($product)->create();
    ProductImage::factory()->recycle($product)->create(['is_primary' => true]);

    $loaded = Category::query()->withActiveProducts()->first()->products->first();

    expect($loaded->relationLoaded('seasonalItems'))->toBeTrue()
        ->and($loaded->relationLoaded('primaryImage'))->toBeTrue();
});
