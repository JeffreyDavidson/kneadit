<?php

use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('order number is generated automatically when not set', function () {
    $order = Order::factory()->make(['order_number' => null]);
    $order->save();

    expect($order->order_number)->toMatch('/^ORD-[A-Z0-9]{10}$/');
});

test('order number is not overwritten when already set', function () {
    $order = Order::factory()->create(['order_number' => 'CUSTOM-001']);

    expect($order->order_number)->toBe('CUSTOM-001');
});
