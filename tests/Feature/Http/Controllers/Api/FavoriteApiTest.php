<?php

use App\Models\CustomerFavorite;
use App\Models\Product;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('favorites index returns product ids for customer email', function () {
    $product = Product::factory()->create();
    CustomerFavorite::factory()->recycle($product)->create([
        'customer_email' => 'alice@test.com',
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson('/api/favorites?email=alice@test.com');

    $response->assertOk()
        ->assertJsonPath('data', [$product->id]);
});

test('favorites index validates email is required', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->getJson('/api/favorites');

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('favorites toggle adds product to favorites', function () {
    $product = Product::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/api/favorites/toggle', [
            'email' => 'alice@test.com',
            'product_id' => $product->id,
        ]);

    $response->assertOk()
        ->assertJsonPath('data.favorited', true);

    $this->assertDatabaseHas('customer_favorites', [
        'customer_email' => 'alice@test.com',
        'product_id' => $product->id,
    ]);
});
