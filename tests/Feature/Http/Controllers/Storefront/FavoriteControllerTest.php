<?php

use App\Models\CustomerFavorite;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\withoutMiddleware;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('can get favorites for an email', function () {
    $product = Product::factory()->create();
    CustomerFavorite::factory()->create([
        'customer_email' => 'jane@example.com',
        'product_id' => $product->id,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson(route('favorites.get', ['email' => 'jane@example.com'], false));

    $response->assertOk()
        ->assertJsonCount(1, 'favorites');
});

test('can toggle a favorite on', function () {
    $product = Product::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson(route('favorites.toggle', [], false), [
            'email' => 'jane@example.com',
            'product_id' => $product->id,
        ]);

    $response->assertOk()
        ->assertJson(['favorited' => true]);

    expect(CustomerFavorite::query()->count())->toBe(1);
});

test('can toggle a favorite off', function () {
    $product = Product::factory()->create();
    CustomerFavorite::factory()->create([
        'customer_email' => 'jane@example.com',
        'product_id' => $product->id,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson(route('favorites.toggle', [], false), [
            'email' => 'jane@example.com',
            'product_id' => $product->id,
        ]);

    $response->assertOk()
        ->assertJson(['favorited' => false]);

    expect(CustomerFavorite::query()->count())->toBe(0);
});
