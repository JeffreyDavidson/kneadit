<?php

use App\Http\Requests\Storefront\PersistCartRequest;
use App\Models\Inventory\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('items must be present (even if empty)', function () {
    $validator = validator([], (new PersistCartRequest)->rules());

    expect($validator->errors()->has('items'))->toBeTrue();
});

test('empty items array is allowed', function () {
    $validator = validator(['items' => []], (new PersistCartRequest)->rules());

    expect($validator->passes())->toBeTrue();
});

test('product_id must reference an existing product', function () {
    $validator = validator([
        'items' => [['product_id' => 999999, 'quantity' => 1]],
    ], (new PersistCartRequest)->rules());

    expect($validator->errors()->has('items.0.product_id'))->toBeTrue();
});

test('quantity bounded 1..20', function (int $qty) {
    $product = Product::factory()->create();

    $validator = validator([
        'items' => [['product_id' => $product->id, 'quantity' => $qty]],
    ], (new PersistCartRequest)->rules());

    expect($validator->errors()->has('items.0.quantity'))->toBeTrue();
})->with([0, 21, -1]);

test('customer_email must be valid when provided', function () {
    $validator = validator([
        'items' => [],
        'customer_email' => 'not-email',
    ], (new PersistCartRequest)->rules());

    expect($validator->errors()->has('customer_email'))->toBeTrue();
});

test('valid cart passes', function () {
    $product = Product::factory()->create();

    $validator = validator([
        'items' => [['product_id' => $product->id, 'quantity' => 2]],
        'customer_email' => 'shopper@example.com',
        'customer_name' => 'Shopper',
    ], (new PersistCartRequest)->rules());

    expect($validator->passes())->toBeTrue();
});
