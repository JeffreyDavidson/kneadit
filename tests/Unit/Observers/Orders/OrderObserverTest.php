<?php

use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('creating sets order number when none is provided', function () {
    $order = Order::factory()->create(['order_number' => null]);

    expect($order->order_number)
        ->toStartWith('ORD-')
        ->toHaveLength(10);
});

test('creating does not overwrite an existing order number', function () {
    $order = Order::factory()->create(['order_number' => 'CUSTOM-001']);

    expect($order->order_number)->toBe('CUSTOM-001');
});

test('creating generates sequential order numbers', function () {
    $first = Order::factory()->create(['order_number' => null]);
    $second = Order::factory()->create(['order_number' => null]);

    $firstNum = (int) substr($first->order_number, 4);
    $secondNum = (int) substr($second->order_number, 4);

    expect($secondNum)->toBe($firstNum + 1);
});
