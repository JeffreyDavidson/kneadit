<?php

use App\Http\Requests\Api\StoreApiFavoriteRequest;
use App\Models\Inventory\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('required fields are enforced', function (string $field) {
    $product = Product::factory()->create();

    $data = ['email' => 'fan@example.com', 'product_id' => $product->id];
    unset($data[$field]);

    $validator = validator($data, (new StoreApiFavoriteRequest)->rules());

    expect($validator->errors()->has($field))->toBeTrue();
})->with(['email', 'product_id']);

test('product_id must reference an existing product', function () {
    $validator = validator([
        'email' => 'fan@example.com',
        'product_id' => 999999,
    ], (new StoreApiFavoriteRequest)->rules());

    expect($validator->errors()->has('product_id'))->toBeTrue();
});

test('valid favorite passes', function () {
    $product = Product::factory()->create();

    $validator = validator([
        'email' => 'fan@example.com',
        'product_id' => $product->id,
    ], (new StoreApiFavoriteRequest)->rules());

    expect($validator->passes())->toBeTrue();
});
