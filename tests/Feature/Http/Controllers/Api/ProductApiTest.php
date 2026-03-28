<?php

use App\Models\Product;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('products endpoint returns active products', function () {
    Product::factory()->count(3)->create(['is_active' => true]);
    Product::factory()->create(['is_active' => false]);

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson('/api/products');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});
