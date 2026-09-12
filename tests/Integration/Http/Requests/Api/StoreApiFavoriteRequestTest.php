<?php

use App\Http\Requests\Api\StoreApiFavoriteRequest;
use App\Models\Inventory\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('required fields are enforced', function () {
    $validator = validator([], (new StoreApiFavoriteRequest)->rules());

    foreach (['email', 'product_id'] as $field) {
        expect($validator->errors()->has($field))->toBeTrue();
    }
});

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
