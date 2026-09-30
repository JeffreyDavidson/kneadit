<?php

use App\Actions\Tenants\LegacyBakeryDataValidator;

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function legacyOrder(array $overrides = []): array
{
    return [
        'id' => 30,
        'order_number' => 'BOB-1',
        'customer_name' => 'Jane Baker',
        'customer_email' => 'jane@example.com',
        ...$overrides,
    ];
}

it('accepts an empty payload', function () {
    $validate = new LegacyBakeryDataValidator;

    expect(fn () => $validate([]))->not->toThrow(Throwable::class);
});

it('accepts a consistent payload with legacy aliases and numeric strings', function () {
    $validate = new LegacyBakeryDataValidator;

    $payload = [
        'categories' => [['id' => '1']],
        'coupons' => [['id' => 2, 'type' => 'fixed_amount', 'code' => 'WELCOME5', 'value' => '5.00']],
        'products' => [['id' => 3, 'category_id' => '1']],
        'orders' => [legacyOrder([
            'status' => 'Pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'other',
            'fulfillment_type' => 'pickup',
            'coupon_id' => '2',
        ])],
        'order_items' => [['order_id' => 30, 'product_name' => 'Loaf', 'quantity' => '2', 'unit_price' => '4.50', 'product_id' => 3]],
    ];

    expect(fn () => $validate($payload))->not->toThrow(Throwable::class);
});

it('rejects records that are missing an ID', function (string $dataset, string $message) {
    $validate = new LegacyBakeryDataValidator;

    expect(fn () => $validate([$dataset => [['name' => 'No ID']]]))
        ->toThrow(InvalidArgumentException::class, $message);
})->with([
    'category' => ['categories', 'Category at index 0 is missing an ID.'],
    'product' => ['products', 'Product at index 0 is missing an ID.'],
    'coupon' => ['coupons', 'Coupon at index 0 is missing an ID.'],
    'recipe' => ['recipes', 'Recipe at index 0 is missing an ID.'],
    'order' => ['orders', 'Order at index 0 is missing an ID.'],
]);

it('rejects duplicate IDs with the offending index', function () {
    $validate = new LegacyBakeryDataValidator;

    expect(fn () => $validate(['orders' => [legacyOrder(), legacyOrder(['order_number' => 'BOB-2'])]]))
        ->toThrow(InvalidArgumentException::class, 'Duplicate order ID 30 at index 1.');
});

it('rejects a product without a category ID', function () {
    $validate = new LegacyBakeryDataValidator;

    expect(fn () => $validate(['products' => [['id' => 3]]]))
        ->toThrow(InvalidArgumentException::class, 'Product at index 0 is missing a category ID.');
});

it('rejects an order without a customer email', function () {
    $validate = new LegacyBakeryDataValidator;

    $order = legacyOrder();
    unset($order['customer_email']);

    expect(fn () => $validate(['orders' => [$order]]))
        ->toThrow(InvalidArgumentException::class, 'Order at index 0 is missing a customer email.');
});

it('rejects unsupported enum values with the normalized value', function (array $payload, string $message) {
    $validate = new LegacyBakeryDataValidator;

    expect(fn () => $validate($payload))->toThrow(InvalidArgumentException::class, $message);
})->with([
    'coupon type' => [
        ['coupons' => [['id' => 2, 'type' => 'BOGO', 'code' => 'X', 'value' => 1]]],
        'Unsupported coupon type [bogo].',
    ],
    'payment status' => [
        ['orders' => [legacyOrder(['payment_status' => 'Refunded-ish'])]],
        'Unsupported payment status [refunded-ish].',
    ],
    'payment method' => [
        ['orders' => [legacyOrder(['payment_method' => 'Barter'])]],
        'Unsupported payment method [barter].',
    ],
    'fulfillment type' => [
        ['orders' => [legacyOrder(['fulfillment_type' => 'teleport'])]],
        'Unsupported fulfillment type [teleport].',
    ],
]);

it('rejects order items that reference a missing order or product', function (array $item, string $message) {
    $validate = new LegacyBakeryDataValidator;

    expect(fn () => $validate([
        'orders' => [legacyOrder()],
        'order_items' => [['product_name' => 'Loaf', 'quantity' => 1, 'unit_price' => 4.5, ...$item]],
    ]))->toThrow(InvalidArgumentException::class, $message);
})->with([
    'order' => [['order_id' => 999], 'Order item at index 0 references missing order ID 999.'],
    'product' => [['order_id' => 30, 'product_id' => 999], 'Order item at index 0 references missing product ID 999.'],
]);

it('rejects recipe children without a recipe ID', function (string $dataset, string $message) {
    $validate = new LegacyBakeryDataValidator;

    expect(fn () => $validate([$dataset => [['name' => 'Orphan']]]))
        ->toThrow(InvalidArgumentException::class, $message);
})->with([
    'ingredient' => ['recipe_ingredients', 'Recipe ingredient at index 0 is missing a recipe ID.'],
    'stage' => ['recipe_stages', 'Recipe stage at index 0 is missing a recipe ID.'],
]);

it('rejects an order note without an order ID and a favorite without a product ID', function (string $dataset, string $message) {
    $validate = new LegacyBakeryDataValidator;

    expect(fn () => $validate([$dataset => [['note' => 'x']]]))
        ->toThrow(InvalidArgumentException::class, $message);
})->with([
    'order note' => ['order_notes', 'Order note at index 0 is missing an order ID.'],
    'customer favorite' => ['customer_favorites', 'Customer favorite at index 0 is missing a product ID.'],
]);

it('rejects values that are not integer or numeric compatible', function (array $payload, string $message) {
    $validate = new LegacyBakeryDataValidator;

    expect(fn () => $validate($payload))->toThrow(UnexpectedValueException::class, $message);
})->with([
    'non-integer ID' => [
        ['categories' => [['id' => 'abc']]],
        'Expected an integer-compatible legacy value.',
    ],
    'non-numeric coupon value' => [
        ['coupons' => [['id' => 2, 'type' => 'fixed', 'code' => 'X', 'value' => 'free']]],
        'Expected a numeric legacy value.',
    ],
    'array coupon code' => [
        ['coupons' => [['id' => 2, 'type' => 'fixed', 'code' => ['X'], 'value' => 1]]],
        'Expected a string-compatible legacy value.',
    ],
]);
