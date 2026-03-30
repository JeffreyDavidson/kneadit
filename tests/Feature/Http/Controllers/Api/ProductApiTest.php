<?php

use App\Models\Product;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('products endpoint returns active products', function () {
    Product::factory()->count(3)->create(['is_active' => true]);
    Product::factory()->inactive()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson('/api/products');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

test('products endpoint filters by featured', function () {
    Product::factory()->count(2)->create(['is_active' => true, 'is_featured' => true]);
    Product::factory()->create(['is_active' => true, 'is_featured' => false]);

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson('/api/products?featured=true');

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});
