<?php

use App\Http\Resources\ProductResource;
use App\Models\Inventory\Category;
use App\Models\Inventory\Product;
use App\Models\Staff\User;

beforeEach(function () {
    setUpTenantTest();
    User::factory()->owner()->create();
});

it('transforms a product into the expected API shape', function () {
    $category = Category::factory()->create(['name' => 'Bread', 'slug' => 'bread', 'is_active' => true]);
    $product = Product::factory()->for($category)->featured()->create([
        'name' => 'Sourdough',
        'slug' => 'sourdough',
        'description' => 'A tangy loaf',
        'price' => 8.50,
        'is_active' => true,
    ]);

    $product->load('category');
    $resource = new ProductResource($product);
    $request = request();

    expect($resource->toType($request))->toBe('products')
        ->and($resource->toId($request))->toBe((string) $product->id);

    $attributes = $resource->toAttributes($request);

    expect($attributes)
        ->toHaveKeys(['name', 'slug', 'description', 'price', 'image', 'is_featured'])
        ->toMatchArray(['name' => 'Sourdough']);
});
