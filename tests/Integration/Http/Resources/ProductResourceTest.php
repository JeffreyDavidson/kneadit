<?php

use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    setUpTenantTest();
    User::query()->create(['name' => 'Test', 'email' => 'test@test.com', 'password' => bcrypt('password')]);
});

it('transforms a product into the expected API shape', function () {
    $category = Category::query()->create(['name' => 'Bread', 'slug' => 'bread', 'is_active' => true]);
    $product = Product::query()->create([
        'name' => 'Sourdough',
        'slug' => 'sourdough',
        'description' => 'A tangy loaf',
        'price' => 8.50,
        'category_id' => $category->id,
        'is_active' => true,
        'is_featured' => true,
    ]);

    $product->load('category');
    $resource = new ProductResource($product);
    $data = $resource->toArray(request());

    expect($data)->toHaveKeys(['id', 'name', 'slug', 'description', 'price', 'image', 'category_id', 'category_name', 'is_featured']);
    expect($data['name'])->toBe('Sourdough');
    expect($data['category_name'])->toBe('Bread');
});
