<?php

use App\Models\Inventory\Category;
use App\Models\Inventory\Product;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('menu endpoint returns categories with active products as JSON:API', function () {
    $category = Category::factory()->create(['is_active' => true]);
    Product::factory()->recycle($category)->count(2)->create(['is_active' => true]);
    Product::factory()->recycle($category)->create(['is_active' => false]);

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson('/api/menu');

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/vnd.api+json')
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.type', 'categories');
});
