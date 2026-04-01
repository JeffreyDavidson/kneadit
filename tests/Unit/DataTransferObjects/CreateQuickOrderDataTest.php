<?php

use App\DataTransferObjects\Orders\CreateQuickOrderData;
use App\Enums\Orders\DeliveryType;

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
        'notes' => 'No nuts',
        'order_items' => [
            ['product_id' => 1, 'quantity' => 2, 'unit_price' => 10.00],
        ],
    ]);

    expect($data->customerName)->toBe('Jane')
        ->and($data->customerEmail)->toBe('jane@example.com')
        ->and($data->deliveryType)->toBe(DeliveryType::Pickup->value)
        ->and($data->orderItems)->toHaveCount(1);
});
