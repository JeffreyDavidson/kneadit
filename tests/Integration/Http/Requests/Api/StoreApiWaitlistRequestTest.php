<?php

use App\Http\Requests\Api\StoreApiWaitlistRequest;
use App\Models\Inventory\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('required fields are enforced', function (string $field) {
    $data = [
        'customer_name' => 'Alice',
        'customer_email' => 'alice@example.com',
        'customer_phone' => '555-0100',
        'requested_date' => now()->addDays(3)->toDateString(),
    ];
    unset($data[$field]);

    $validator = validator($data, (new StoreApiWaitlistRequest)->rules());

    expect($validator->errors()->has($field))->toBeTrue();
})->with(['customer_name', 'customer_email', 'customer_phone', 'requested_date']);

test('product_id must reference an existing product when provided', function () {
    $validator = validator([
        'customer_name' => 'Alice',
        'customer_email' => 'alice@example.com',
        'customer_phone' => '555-0100',
        'requested_date' => now()->addDays(3)->toDateString(),
        'product_id' => 999999,
    ], (new StoreApiWaitlistRequest)->rules());

    expect($validator->errors()->has('product_id'))->toBeTrue();
});

test('requested_date must be a valid date', function () {
    $validator = validator([
        'customer_name' => 'Alice',
        'customer_email' => 'alice@example.com',
        'customer_phone' => '555-0100',
        'requested_date' => 'not-a-date',
    ], (new StoreApiWaitlistRequest)->rules());

    expect($validator->errors()->has('requested_date'))->toBeTrue();
});

test('valid waitlist request passes', function () {
    $product = Product::factory()->create();

    $validator = validator([
        'customer_name' => 'Alice',
        'customer_email' => 'alice@example.com',
        'customer_phone' => '555-0100',
        'requested_date' => now()->addDays(3)->toDateString(),
        'product_id' => $product->id,
    ], (new StoreApiWaitlistRequest)->rules());

    expect($validator->passes())->toBeTrue();
});
