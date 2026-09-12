<?php

use App\Models\Inventory\Category;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('categories endpoint returns active categories as JSON:API', function () {
    Category::factory()->count(2)->create(['is_active' => true]);
    Category::factory()->inactive()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson('/api/categories');

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/vnd.api+json')
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.type', 'categories')
        ->assertJsonStructure([
            'data' => [
                ['id', 'type', 'attributes' => ['name', 'slug', 'description', 'sort_order']],
            ],
        ]);
});
