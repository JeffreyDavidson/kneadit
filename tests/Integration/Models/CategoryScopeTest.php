<?php

use App\Models\Inventory\Category;
use App\Models\Inventory\Product;
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
