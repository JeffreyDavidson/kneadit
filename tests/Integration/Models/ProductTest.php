<?php

use App\Models\Content\SocialPost;
use App\Models\Customers\CustomerPhoto;
use App\Models\Engagement\PageView;
use App\Models\Inventory\Category;
use App\Models\Inventory\Product;
use App\Models\Inventory\ProductImage;
use App\Models\Inventory\ProductWaitlist;
use App\Models\Inventory\Recipe;
use App\Models\Inventory\SeasonalItem;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    User::factory()->owner()->create();
});

test('product belongs to category', function () {
    $category = Category::factory()->create(['name' => 'Cakes']);
    $product = Product::factory()->for($category)->create(['name' => 'Chocolate Cake']);

    expect($product->category)->toBeInstanceOf(Category::class)->and($product->category->name)->toBe('Cakes');
});

test('product has recipes relationship', function () {
    $product = Product::factory()->create(['name' => 'Sourdough']);

    Recipe::factory()->for($product)->create(['name' => 'Sourdough Recipe']);

    expect($product->recipes)->toHaveCount(1);
});

test('product has seasonal items', function () {
    $product = Product::factory()->create(['name' => 'Pumpkin Pie']);

    SeasonalItem::factory()->for($product)->create();

    expect($product->seasonalItems)->toHaveCount(1);
});

test('is in season returns true for current seasonal products', function () {
    $product = Product::factory()->create(['name' => 'Pumpkin Pie']);

    SeasonalItem::factory()->for($product)->current()->create();

    $product->load('seasonalItems');

    expect($product->is_in_season)->toBeTrue();
});

test('is in season returns false for out of season products', function () {
    $product = Product::factory()->create(['name' => 'Pumpkin Pie']);

    SeasonalItem::factory()->for($product)->expired()->create();

    $product->load('seasonalItems');

    expect($product->is_in_season)->toBeFalse();
});

test('product with no seasonal entries is always in season', function () {
    $product = Product::factory()->create(['name' => 'Sourdough']);

    $product->load('seasonalItems');

    expect($product->is_in_season)->toBeTrue();
});

test('product margin attribute', function () {
    $product = Product::factory()->create(['name' => 'Sourdough', 'price' => 10.00, 'cost' => 4.00]);

    expect($product->margin)->toBe(60.0);
});

test('product margin is null without cost', function () {
    $product = Product::factory()->create(['name' => 'Sourdough', 'price' => 10.00]);

    expect($product->margin)->toBeNull();
});

test('primary image url returns primary image path', function () {
    $product = Product::factory()->create(['name' => 'Test Product', 'image' => null]);

    ProductImage::factory()->primary()->for($product)->create(['path' => 'products/primary.jpg']);

    $product = $product->fresh('primaryImage');

    expect($product->primary_image_url)->toContain('primary.jpg');
});

test('primary image url falls back to image column', function () {
    $product = Product::factory()->create(['name' => 'Test Product', 'image' => 'products/fallback.jpg']);

    $product->load('primaryImage');

    expect($product->primary_image_url)->toContain('fallback.jpg');
});

test('primary image url returns null when no images exist', function () {
    $product = Product::factory()->create(['name' => 'Test Product', 'image' => null]);

    $product->load('primaryImage');

    expect($product->primary_image_url)->toBeNull();
});

test('product has customer photos relationship', function () {
    $product = Product::factory()->create(['name' => 'Photo Product']);

    CustomerPhoto::factory()->create(['product_id' => $product->id]);

    expect($product->customerPhotos)->toHaveCount(1);
});

test('product has page views relationship', function () {
    $product = Product::factory()->create(['name' => 'Popular Product']);

    PageView::factory()->for($product)->create();

    expect($product->pageViews)->toHaveCount(1);
});

test('product has social posts relationship', function () {
    $product = Product::factory()->create(['name' => 'Social Product']);

    SocialPost::factory()->create(['product_id' => $product->id]);

    expect($product->socialPosts)->toHaveCount(1);
});

test('product has waitlist entries relationship', function () {
    $product = Product::factory()->create(['name' => 'Waitlist Product']);

    ProductWaitlist::factory()->for($product)->create();

    expect($product->waitlistEntries)->toHaveCount(1);
});

test('seasonal badge returns limited time for current seasonal item', function () {
    $product = Product::factory()->create(['name' => 'Seasonal Product']);

    SeasonalItem::factory()->for($product)->current()->create();

    $product->load('seasonalItems');

    expect($product->seasonal_badge)->toBe('Limited Time');
});

test('seasonal badge returns availability range for upcoming seasonal item', function () {
    $product = Product::factory()->create(['name' => 'Upcoming Product']);

    SeasonalItem::factory()->for($product)->upcoming()->create();

    $product->load('seasonalItems');

    expect($product->seasonal_badge)->toContain('Available');
});

test('seasonal badge returns null for non seasonal product', function () {
    $product = Product::factory()->create(['name' => 'Regular Product']);

    $product->load('seasonalItems');

    expect($product->seasonal_badge)->toBeNull();
});
