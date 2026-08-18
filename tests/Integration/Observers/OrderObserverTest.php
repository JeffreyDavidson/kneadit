<?php

use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('order number is auto-generated on create', function () {
    $order = Order::factory()->create(['order_number' => null]);

    expect($order->order_number)->toStartWith('ORD-');
});

test('order number is not overwritten if already set', function () {
    $order = Order::factory()->create(['order_number' => 'CUSTOM-001']);

    expect($order->order_number)->toBe('CUSTOM-001');
});
