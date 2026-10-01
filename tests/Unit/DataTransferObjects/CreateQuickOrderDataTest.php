<?php

use App\DataTransferObjects\Orders\CreateQuickOrderData;
use App\Enums\Orders\DeliveryType;
use App\Enums\Orders\PaymentMethod;

test('it can be created from array', function () {
    $data = CreateQuickOrderData::fromArray([
        'customer_name' => 'Jane',
        'customer_email' => 'jane@example.com',
        'customer_phone' => '555-1234',
        'payment_method' => 'cash',
        'delivery_type' => DeliveryType::Pickup->value,
        'delivery_date' => '2026-04-01',
        'delivery_time' => '10:00',
        'delivery_address' => null,
        'delivery_tier' => '1',
        'notes' => 'No nuts',
        'order_items' => [
            ['product_id' => 1, 'quantity' => 2, 'unit_price' => 10.00],
        ],
    ]);

    expect($data->customerName)->toBe('Jane')
        ->and($data->customerEmail)->toBe('jane@example.com')
        ->and($data->paymentMethod)->toBe(PaymentMethod::Cash)
        ->and($data->deliveryType)->toBe(DeliveryType::Pickup)
        ->and($data->deliveryTier)->toBe('1')
        ->and($data->orderItems)->toHaveCount(1);
});

test('it accepts the enum instances Filament selects return', function () {
    $data = CreateQuickOrderData::fromArray([
        'customer_name' => 'Jane',
        'customer_email' => 'jane@example.com',
        'payment_method' => PaymentMethod::Cash,
        'delivery_type' => DeliveryType::Delivery,
        'delivery_tier' => 1,
        'delivery_date' => '2026-04-01',
        'delivery_time' => '10:00',
        'order_items' => [],
    ]);

    expect($data->paymentMethod)->toBe(PaymentMethod::Cash)
        ->and($data->deliveryType)->toBe(DeliveryType::Delivery)
        ->and($data->deliveryTier)->toBe('1');
});

test('it rejects an unknown enum value', function (string $key) {
    CreateQuickOrderData::fromArray([
        'customer_name' => 'Jane',
        'customer_email' => 'jane@example.com',
        'payment_method' => 'cash',
        'delivery_type' => 'pickup',
        'delivery_date' => '2026-04-01',
        'delivery_time' => '10:00',
        'order_items' => [],
        $key => 'nope',
    ]);
})->with(['payment_method', 'delivery_type'])->throws(UnexpectedValueException::class);

test('it requires a customer email', function () {
    CreateQuickOrderData::fromArray([
        'customer_name' => 'Jane',
        'payment_method' => 'cash',
        'delivery_type' => 'pickup',
        'delivery_date' => '2026-04-01',
        'delivery_time' => '10:00',
        'order_items' => [],
    ]);
})->throws(UnexpectedValueException::class, 'Expected customer_email to be a string.');

test('it accepts a whole-number float quantity', function () {
    $data = CreateQuickOrderData::fromArray([
        'customer_name' => 'Jane',
        'customer_email' => 'jane@example.com',
        'payment_method' => 'cash',
        'delivery_type' => 'pickup',
        'delivery_date' => '2026-04-01',
        'delivery_time' => '10:00',
        'order_items' => [
            ['product_id' => 1, 'quantity' => 2.0, 'unit_price' => 10.00],
        ],
    ]);

    expect($data->orderItems[0]['quantity'])->toBe(2);
});
