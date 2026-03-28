<?php

use App\Models\Category;
use App\Models\Product;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('menu endpoint returns categories with active products', function () {
    $category = Category::factory()->create(['is_active' => true]);
    Product::factory()->count(2)->create(['category_id' => $category->id, 'is_active' => true]);
    Product::factory()->create(['category_id' => $category->id, 'is_active' => false]);

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson('/api/menu');

    $response->assertOk()
        ->assertJsonCount(1, 'data');
});
