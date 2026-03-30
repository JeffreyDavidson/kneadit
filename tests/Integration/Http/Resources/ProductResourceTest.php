<?php

use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;

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
    $data = $resource->toArray(request());

    expect($data)->toHaveKeys(['id', 'name', 'slug', 'description', 'price', 'image', 'category_id', 'category_name', 'is_featured'])->toMatchArray(['name' => 'Sourdough', 'category_name' => 'Bread']);
});
