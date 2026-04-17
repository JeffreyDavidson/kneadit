<?php

use App\Models\Customers\CustomerFavorite;
use App\Models\Inventory\Product;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('favorites index returns favorites for customer email as JSON:API', function () {
    $product = Product::factory()->create();
    CustomerFavorite::factory()->recycle($product)->create([
        'customer_email' => 'alice@test.com',
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson('/api/favorites?email=alice@test.com');

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/vnd.api+json')
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.type', 'favorites')
        ->assertJsonPath('data.0.attributes.customer_email', 'alice@test.com')
        ->assertJsonPath('data.0.attributes.product_id', $product->id);
});

test('favorites index validates email is required', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->getJson('/api/favorites');

    $response->assertUnprocessable()
        ->assertJsonPath('errors.0.source.pointer', '/data/attributes/email');
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

    test()->assertDatabaseHas('customer_favorites', [
        'customer_email' => 'alice@test.com',
        'product_id' => $product->id,
    ]);
});
