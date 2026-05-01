<?php

use App\Http\Requests\Storefront\StoreProductWaitlistRequest;
use App\Models\Inventory\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('required fields are enforced', function (string $field) {
    $product = Product::factory()->create();

    $data = ['product_id' => $product->id, 'customer_email' => 'a@b.com'];
    unset($data[$field]);

    $validator = validator($data, (new StoreProductWaitlistRequest)->rules());

    expect($validator->errors()->has($field))->toBeTrue();
})->with(['product_id', 'customer_email']);

test('product_id must reference an existing product', function () {
    $validator = validator([
        'product_id' => 999999,
        'customer_email' => 'a@b.com',
    ], (new StoreProductWaitlistRequest)->rules());

    expect($validator->errors()->has('product_id'))->toBeTrue();
});

test('customer_email must be valid', function () {
    $product = Product::factory()->create();

    $validator = validator([
        'product_id' => $product->id,
        'customer_email' => 'not-email',
    ], (new StoreProductWaitlistRequest)->rules());

    expect($validator->errors()->has('customer_email'))->toBeTrue();
});

test('valid signup passes', function () {
    $product = Product::factory()->create();

    $validator = validator([
        'product_id' => $product->id,
        'customer_email' => 'wishful@example.com',
        'customer_name' => 'Wishful',
    ], (new StoreProductWaitlistRequest)->rules());

    expect($validator->passes())->toBeTrue();
});
