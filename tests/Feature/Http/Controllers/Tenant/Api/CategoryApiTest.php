<?php

use App\Models\Inventory\Category;
use App\Models\Inventory\Product;

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

test('categories include only lists active products', function () {
    $category = Category::factory()->active()->create();
    $active = Product::factory()->recycle($category)->active()->create();
    $inactive = Product::factory()->recycle($category)->inactive()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson('/api/categories?include=products');

    $response->assertOk();

    expect(collect($response->json('included'))->pluck('id')->all())->toBe([(string) $active->id])
        ->and($response->getContent())->not->toContain($inactive->name);
});

test('categories ignore an unknown include', function () {
    Category::factory()->active()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson('/api/categories?include=users');

    $response->assertOk()->assertJsonCount(1, 'data');
});
