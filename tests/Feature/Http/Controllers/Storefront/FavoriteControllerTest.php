<?php

use App\Models\Customers\CustomerFavorite;
use App\Models\Inventory\Product;
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
        ->getJson(route('api.favorites.index', ['email' => 'jane@example.com'], false));

    $response->assertOk()
        ->assertJsonCount(1, 'data');
});

test('can toggle a favorite on', function () {
    $product = Product::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson(route('api.favorites.toggle', [], false), [
            'email' => 'jane@example.com',
            'product_id' => $product->id,
        ]);

    $response->assertOk()
        ->assertJsonPath('data.favorited', true);

    expect(CustomerFavorite::query()->count())->toBe(1);
});

test('can toggle a favorite off', function () {
    $product = Product::factory()->create();
    CustomerFavorite::factory()->create([
        'customer_email' => 'jane@example.com',
        'product_id' => $product->id,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson(route('api.favorites.toggle', [], false), [
            'email' => 'jane@example.com',
            'product_id' => $product->id,
        ]);

    $response->assertOk()
        ->assertJsonPath('data.favorited', false);

    expect(CustomerFavorite::query()->count())->toBe(0);
});
