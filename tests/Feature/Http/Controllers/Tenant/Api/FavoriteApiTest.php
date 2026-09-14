<?php

use App\Models\Customers\Customer;
use App\Models\Customers\CustomerFavorite;
use App\Models\Inventory\Product;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('favorites index returns favorites for customer email as JSON:API', function () {
    $customer = Customer::factory()->create(['email' => 'alice@test.com']);
    $product = Product::factory()->create();
    CustomerFavorite::factory()->recycle($product)->create([
        'customer_email' => 'alice@test.com',
    ]);
    CustomerFavorite::factory()->recycle($product)->create(['customer_email' => 'other@test.com']);

    $response = withoutMiddleware(tenantMiddleware())
        ->actingAs($customer, 'customer')
        ->getJson('/api/favorites?email=other@test.com');

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/vnd.api+json')
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.type', 'favorites')
        ->assertJsonPath('data.0.attributes.customer_email', 'alice@test.com')
        ->assertJsonPath('data.0.attributes.product_id', $product->id);
});

test('favorites endpoints require a customer account', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->getJson('/api/favorites');

    $response->assertUnauthorized();
});

test('favorites toggle adds product to favorites and returns JSON:API envelope', function () {
    $customer = Customer::factory()->create(['email' => 'alice@test.com']);
    $product = Product::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->actingAs($customer, 'customer')
        ->postJson('/api/favorites/toggle', [
            'email' => 'other@test.com',
            'product_id' => $product->id,
        ]);

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/vnd.api+json')
        ->assertJsonPath('data.type', 'favorite-toggles')
        ->assertJsonPath('data.attributes.favorited', true)
        ->assertJsonPath('data.attributes.customer_email', 'alice@test.com')
        ->assertJsonPath('data.attributes.product_id', $product->id);

    test()->assertDatabaseHas('customer_favorites', [
        'customer_email' => 'alice@test.com',
        'product_id' => $product->id,
    ]);
});

test('favorites cannot be toggled for another customer', function () {
    $customer = Customer::factory()->create(['email' => 'alice@test.com']);
    $product = Product::factory()->create();

    withoutMiddleware(tenantMiddleware())
        ->actingAs($customer, 'customer')
        ->postJson('/api/favorites/toggle', [
            'email' => 'other@test.com',
            'product_id' => $product->id,
        ])
        ->assertJsonPath('data.attributes.customer_email', 'alice@test.com');

    expect(CustomerFavorite::query()->where('customer_email', 'other@test.com')->exists())->toBeFalse();
});
