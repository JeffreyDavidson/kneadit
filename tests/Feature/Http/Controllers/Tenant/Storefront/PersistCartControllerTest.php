<?php

use App\Models\Customers\Customer;
use App\Models\Inventory\Product;
use App\Models\Orders\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\withoutMiddleware;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('persists items + email and returns the cart token', function () {
    $product = Product::factory()->create(['price' => 9.50]);

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/cart', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
            'customer_email' => 'alice@example.com',
            'customer_name' => 'Alice',
        ]);

    $response->assertOk();
    expect($response->json('data.cart_token'))->toBeString()->not->toBeEmpty()
        ->and($response->json('data.item_count'))->toBe(1);

    $cart = Cart::query()->latest('id')->first();
    expect($cart->customer_email)->toBe('alice@example.com')
        ->and($cart->customer_name)->toBe('Alice')
        ->and($cart->items()->first()->product_id)->toBe($product->id)
        ->and($cart->items()->first()->quantity)->toBe(2);
});

test('a guest cart is not tied to any customer', function () {
    $product = Product::factory()->create(['price' => 9.50]);
    Customer::factory()->verified()->create(['email' => 'alice@example.com']);

    withoutMiddleware(tenantMiddleware())
        ->postJson('/cart', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'customer_email' => 'alice@example.com',
            'customer_name' => 'Anything',
        ])
        ->assertOk();

    expect(Cart::query()->sole()->customer_id)->toBeNull();
});

test('a signed-in customer cart is tied to them and takes the contact details from their record', function () {
    $product = Product::factory()->create(['price' => 9.50]);
    $customer = Customer::factory()->create(['email' => 'ada@example.com', 'name' => 'Ada Lovelace']);

    actingAs($customer, 'customer');
    withoutMiddleware(tenantMiddleware())
        ->postJson('/cart', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'customer_email' => 'victim@example.com',
            'customer_name' => 'Click here',
        ])
        ->assertOk();

    $cart = Cart::query()->sole();
    expect($cart->customer_id)->toBe($customer->id)
        ->and($cart->customer_email)->toBe('ada@example.com')
        ->and($cart->customer_name)->toBe('Ada Lovelace');
});

test('creates a new cart on first post and returns a token', function () {
    $product = Product::factory()->create(['price' => 5.00]);

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/cart', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

    $response->assertOk();
    expect($response->json('data.cart_token'))->toBeString()->not->toBeEmpty()
        ->and(Cart::query()->count())->toBe(1);
    // Cookie round-trip behavior (reusing the same cart via cookie) is covered
    // by CartManagerTest which sets the cookie directly on the Request object —
    // Laravel's JSON test client is awkward to use for signed-cookie round-trips.
});

test('rejects items with invalid product_id', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/cart', [
            'items' => [
                ['product_id' => 99999, 'quantity' => 1],
            ],
        ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['items.0.product_id']);
});

test('accepts an empty items array (clears cart)', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/cart', ['items' => []]);

    $response->assertOk();
    expect($response->json('data.item_count'))->toBe(0);
});
