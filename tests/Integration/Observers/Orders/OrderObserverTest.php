<?php

use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('creating sets order number when none is provided', function () {
    $order = Order::factory()->create(['order_number' => null]);

    expect($order->order_number)->toMatch('/^ORD-[A-Z0-9]{10}$/');
});

test('creating does not overwrite an existing order number', function () {
    $order = Order::factory()->create(['order_number' => 'CUSTOM-001']);

    expect($order->order_number)->toBe('CUSTOM-001');
});

test('creating generates unique order numbers', function () {
    $first = Order::factory()->create(['order_number' => null]);
    $second = Order::factory()->create(['order_number' => null]);

    expect($second->order_number)->not->toBe($first->order_number);
});
