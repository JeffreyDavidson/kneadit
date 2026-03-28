<?php

use App\Models\CustomerFavorite;
use App\Models\Product;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('favorites index returns product ids for customer email', function () {
    $product = Product::factory()->create();
    CustomerFavorite::query()->create([
        'customer_email' => 'alice@test.com',
        'product_id' => $product->id,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson('/api/favorites?email=alice@test.com');

    $response->assertOk()
        ->assertJsonPath('data', [$product->id]);
});
