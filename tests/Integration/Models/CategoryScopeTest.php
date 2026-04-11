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
    $category = Category::factory()->create(['is_active' => true]);
    Product::factory()->create(['category_id' => $category->id, 'is_active' => true, 'is_featured' => true, 'name' => 'Featured']);
    Product::factory()->create(['category_id' => $category->id, 'is_active' => true, 'is_featured' => false, 'name' => 'Regular']);
    Product::factory()->create(['category_id' => $category->id, 'is_active' => false, 'is_featured' => true, 'name' => 'Inactive']);

    $result = Category::query()->withFeaturedProducts()->first();

    expect($result->products)->toHaveCount(1)
        ->and($result->products->first()->name)->toBe('Featured');
});
