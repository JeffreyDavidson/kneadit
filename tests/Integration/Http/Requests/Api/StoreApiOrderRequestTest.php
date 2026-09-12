<?php

use App\Http\Requests\Api\StoreApiOrderRequest;
use App\Models\Inventory\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('required top-level fields are enforced', function () {
    $validator = validator([], (new StoreApiOrderRequest)->rules());

    foreach (['customer_name', 'customer_email', 'items', 'delivery_date', 'delivery_type'] as $field) {
        expect($validator->errors()->has($field))->toBeTrue();
    }
});

test('items must contain at least one row', function () {
    $validator = validator(
        array_merge(validApiOrderData(), ['items' => []]),
        (new StoreApiOrderRequest)->rules(),
    );

    expect($validator->errors()->has('items'))->toBeTrue();
});

test('item product_id must reference an existing product', function () {
    $validator = validator(
        array_merge(validApiOrderData(), [
            'items' => [['product_id' => 999999, 'quantity' => 1]],
        ]),
        (new StoreApiOrderRequest)->rules(),
    );

    expect($validator->errors()->has('items.0.product_id'))->toBeTrue();
});

test('item quantity bounded 1..20', function () {
    $data = validApiOrderData();
    $productId = $data['items'][0]['product_id'];

    foreach ([0, 21, -1] as $quantity) {
        $validator = validator(
            array_merge($data, [
                'items' => [['product_id' => $productId, 'quantity' => $quantity]],
            ]),
            (new StoreApiOrderRequest)->rules(),
        );

        expect($validator->errors()->has('items.0.quantity'))->toBeTrue();
    }
});

test('delivery_type must be pickup or delivery', function () {
    $validator = validator(
        array_merge(validApiOrderData(), ['delivery_type' => 'shipping']),
        (new StoreApiOrderRequest)->rules(),
    );

    expect($validator->errors()->has('delivery_type'))->toBeTrue();
});

test('delivery_address is required when delivery_type is delivery', function () {
    $validator = validator(
        array_merge(validApiOrderData(), [
            'delivery_type' => 'delivery',
            'delivery_address' => null,
            'delivery_tier' => 'under5',
        ]),
        (new StoreApiOrderRequest)->rules(),
    );

    expect($validator->errors()->has('delivery_address'))->toBeTrue();
});

test('delivery_tier must match the enum', function () {
    $validator = validator(
        array_merge(validApiOrderData(), [
            'delivery_type' => 'delivery',
            'delivery_address' => '123 Main St',
            'delivery_tier' => 'extreme-distance',
        ]),
        (new StoreApiOrderRequest)->rules(),
    );

    expect($validator->errors()->has('delivery_tier'))->toBeTrue();
});

test('tip_amount bounded 0..1000', function () {
    $data = validApiOrderData();

    foreach ([-1, 1001, 'abc'] as $tip) {
        $validator = validator(
            array_merge($data, ['tip_amount' => $tip]),
            (new StoreApiOrderRequest)->rules(),
        );

        expect($validator->errors()->has('tip_amount'))->toBeTrue();
    }
});

test('valid pickup order passes', function () {
    $validator = validator(validApiOrderData(), (new StoreApiOrderRequest)->rules());

    expect($validator->passes())->toBeTrue();
});

function validApiOrderData(): array
{
    $product = Product::factory()->create();

    return [
        'customer_name' => 'Alice',
        'customer_email' => 'alice@example.com',
        'items' => [
            ['product_id' => $product->id, 'quantity' => 2],
        ],
        'delivery_date' => now()->addDays(7)->toDateString(),
        'delivery_type' => 'pickup',
    ];
}
