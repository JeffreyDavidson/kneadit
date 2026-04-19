<?php

use App\Models\Inventory\Category;
use App\Models\Inventory\Product;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('menu endpoint returns categories with active products as JSON:API', function () {
    $category = Category::factory()->active()->create();
    Product::factory()->recycle($category)->active()->count(2)->create();
    Product::factory()->recycle($category)->inactive()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson('/api/menu');

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/vnd.api+json')
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.type', 'categories');
});
