<?php

use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('order number is generated automatically when not set', function () {
    $order = Order::factory()->make(['order_number' => null]);
    $order->save();

    expect($order->order_number)->toStartWith('ORD-')
        ->and(strlen($order->order_number))->toBeGreaterThanOrEqual(10);
});

test('order number increments from highest existing number', function () {
    // Create an order with a known high number
    Order::factory()->create(['order_number' => 'ORD-999990']);

    // Create a new order without specifying order_number
    $order = Order::factory()->make(['order_number' => null]);
    $order->save();

    expect($order->order_number)->toBe('ORD-999991');
});

test('order number is not overwritten when already set', function () {
    $order = Order::factory()->create(['order_number' => 'CUSTOM-001']);

    expect($order->order_number)->toBe('CUSTOM-001');
});
