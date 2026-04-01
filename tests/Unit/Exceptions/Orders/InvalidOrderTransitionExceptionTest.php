<?php

use App\Enums\Orders\OrderStatus;
use App\Exceptions\Orders\InvalidOrderTransitionException;
use App\Models\Orders\Order;

test('stores order and status context', function () {
    $order = new Order;
    $order->id = 42;
    $order->order_number = 'ORD-000042';

    $exception = new InvalidOrderTransitionException($order, OrderStatus::Pending, OrderStatus::Delivered);

    expect($exception->getMessage())->toBe('Cannot change status from pending to delivered')
        ->and($exception->order)->toBe($order)
        ->and($exception->from)->toBe(OrderStatus::Pending)
        ->and($exception->to)->toBe(OrderStatus::Delivered)
        ->and($exception->context())->toBe([
            'order_id' => 42,
            'order_number' => 'ORD-000042',
            'from' => 'pending',
            'to' => 'delivered',
        ]);
});
