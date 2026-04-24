<?php

use App\Models\Inventory\Product;
use App\Models\Orders\Cart;
use App\Models\Orders\CartItem;
use App\Services\Carts\CartManager;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('current returns null when no cookie is present', function () {
    expect(resolve(CartManager::class)->current())->toBeNull();
});

test('currentOrCreate creates a new cart with a token when none exists', function () {
    $cart = resolve(CartManager::class)->currentOrCreate();

    expect($cart->cart_token)->toBeString()->not->toBeEmpty()
        ->and($cart->last_activity_at)->not->toBeNull()
        ->and($cart->expires_at?->isAfter(now()))->toBeTrue();
});

test('current returns the cart matching the cookie', function () {
    $existing = Cart::factory()->create();
    request()->cookies->set('cart_token', $existing->cart_token);

    $cart = resolve(CartManager::class)->current();

    expect($cart?->is($existing))->toBeTrue();
});

test('current ignores converted carts', function () {
    $cart = Cart::factory()->converted()->create();
    request()->cookies->set('cart_token', $cart->cart_token);

    expect(resolve(CartManager::class)->current())->toBeNull();
});

test('replaceItems wipes existing items and inserts the new set', function () {
    $cart = Cart::factory()->create();
    $oldProduct = Product::factory()->create(['price' => 5.00]);
    CartItem::factory()->for($cart)->create(['product_id' => $oldProduct->id, 'quantity' => 99]);

    $newProduct = Product::factory()->create(['price' => 12.50]);

    resolve(CartManager::class)->replaceItems($cart, [
        ['product_id' => $newProduct->id, 'quantity' => 3],
    ]);

    $items = $cart->items()->get();
    expect($items)->toHaveCount(1)
        ->and($items->first()->product_id)->toBe($newProduct->id)
        ->and($items->first()->quantity)->toBe(3)
        ->and($items->first()->unit_price->dollars())->toBe(12.50);
});

test('replaceItems with an empty array clears the cart', function () {
    $cart = Cart::factory()->create();
    CartItem::factory()->for($cart)->count(2)->create();

    resolve(CartManager::class)->replaceItems($cart, []);

    expect($cart->items()->count())->toBe(0);
});

test('replaceItems skips items referencing missing products', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 8.00]);

    resolve(CartManager::class)->replaceItems($cart, [
        ['product_id' => $product->id, 'quantity' => 1],
        ['product_id' => 99999, 'quantity' => 2],
    ]);

    expect($cart->items()->count())->toBe(1);
});

test('updateContact normalizes whitespace and persists', function () {
    $cart = Cart::factory()->create();

    resolve(CartManager::class)->updateContact($cart, '  alice@example.com  ', '  Alice  ');

    expect($cart->fresh())
        ->customer_email->toBe('alice@example.com')
        ->customer_name->toBe('Alice');
});

test('updateContact treats empty strings as null', function () {
    $cart = Cart::factory()->withEmail('alice@example.com')->create();

    resolve(CartManager::class)->updateContact($cart, '   ', '');

    expect($cart->fresh()->customer_email)->toBeNull()
        ->and($cart->fresh()->customer_name)->toBeNull();
});

test('touch refreshes last_activity_at', function () {
    $cart = Cart::factory()->abandoned(48)->create();
    $previousActivity = $cart->last_activity_at;

    resolve(CartManager::class)->touch($cart);

    expect($cart->fresh()->last_activity_at?->isAfter($previousActivity))->toBeTrue();
});
