<?php

use App\Http\Requests\Api\StoreApiReviewRequest;
use App\Models\Inventory\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('required fields are enforced', function (string $field) {
    $product = Product::factory()->create();

    $data = [
        'customer_name' => 'Alice',
        'customer_email' => 'alice@example.com',
        'product_id' => $product->id,
        'rating' => 5,
        'comment' => 'Loved it',
    ];
    unset($data[$field]);

    $validator = validator($data, (new StoreApiReviewRequest)->rules());

    expect($validator->errors()->has($field))->toBeTrue();
})->with(['customer_name', 'customer_email', 'product_id', 'rating', 'comment']);

test('rating bounded 1..5', function (int $rating) {
    $product = Product::factory()->create();

    $validator = validator([
        'customer_name' => 'Alice',
        'customer_email' => 'alice@example.com',
        'product_id' => $product->id,
        'rating' => $rating,
        'comment' => 'meh',
    ], (new StoreApiReviewRequest)->rules());

    expect($validator->errors()->has('rating'))->toBeTrue();
})->with([0, 6, -1]);

test('product_id must reference an existing product', function () {
    $validator = validator([
        'customer_name' => 'Alice',
        'customer_email' => 'alice@example.com',
        'product_id' => 999999,
        'rating' => 5,
        'comment' => 'Loved it',
    ], (new StoreApiReviewRequest)->rules());

    expect($validator->errors()->has('product_id'))->toBeTrue();
});

test('valid review passes', function () {
    $product = Product::factory()->create();

    $validator = validator([
        'customer_name' => 'Alice',
        'customer_email' => 'alice@example.com',
        'product_id' => $product->id,
        'rating' => 5,
        'comment' => 'Loved it',
    ], (new StoreApiReviewRequest)->rules());

    expect($validator->passes())->toBeTrue();
});
