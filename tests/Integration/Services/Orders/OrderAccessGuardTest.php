<?php

use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Services\Orders\OrderAccessGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('canAccess returns false when no session grant exists and no auth match', function () {
    $order = Order::factory()->create();

    expect(OrderAccessGuard::canAccess($order))->toBeFalse();
});

test('grant adds the order_number to the session-tracked list', function () {
    $order = Order::factory()->create();

    OrderAccessGuard::grant($order);

    expect(OrderAccessGuard::canAccess($order))->toBeTrue();
});

test('grant is idempotent — granting twice does not duplicate', function () {
    $order = Order::factory()->create();

    OrderAccessGuard::grant($order);
    OrderAccessGuard::grant($order);

    expect(session('verified_order_numbers'))->toBe([$order->order_number]);
});

test('canAccess is per-order — granting one order does not unlock another', function () {
    $orderA = Order::factory()->create();
    $orderB = Order::factory()->create();

    OrderAccessGuard::grant($orderA);

    expect(OrderAccessGuard::canAccess($orderA))->toBeTrue()
        ->and(OrderAccessGuard::canAccess($orderB))->toBeFalse();
});

test('authenticated customer matching the orders customer_id bypasses the session check', function () {
    $customer = Customer::factory()->create();
    $order = Order::factory()->for($customer)->create();

    auth('customer')->login($customer);

    expect(OrderAccessGuard::canAccess($order))->toBeTrue();
});

test('authenticated customer who does NOT own the order is still blocked', function () {
    $owner = Customer::factory()->create();
    $other = Customer::factory()->create();
    $order = Order::factory()->for($owner)->create();

    auth('customer')->login($other);

    expect(OrderAccessGuard::canAccess($order))->toBeFalse();
});
