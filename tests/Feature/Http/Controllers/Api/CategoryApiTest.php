<?php

use App\Models\Category;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('categories endpoint returns active categories', function () {
    Category::factory()->count(2)->create(['is_active' => true]);
    Category::factory()->create(['is_active' => false]);

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson('/api/categories');

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});
