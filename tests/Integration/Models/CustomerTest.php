<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->user = User::factory()->owner()->create();
});

test('lifetime value sums non cancelled orders', function () {
    $customer = Customer::factory()->create();
    Order::factory()->for($customer)->recycle(test()->user)->delivered()->create(['total' => 50.00, 'subtotal' => 50]);
    Order::factory()->for($customer)->recycle(test()->user)->confirmed()->create(['total' => 30.00, 'subtotal' => 30]);

    expect($customer->lifetime_value)->toBe(80.00);
});

test('lifetime value excludes cancelled orders', function () {
    $customer = Customer::factory()->create();
    Order::factory()->for($customer)->recycle(test()->user)->delivered()->create(['total' => 50.00, 'subtotal' => 50]);
    Order::factory()->for($customer)->recycle(test()->user)->cancelled()->create(['total' => 30.00, 'subtotal' => 30]);

    expect($customer->lifetime_value)->toBe(50.00);
});

test('order count counts non cancelled orders', function () {
    $customer = Customer::factory()->create();
    Order::factory()->for($customer)->recycle(test()->user)->delivered()->create(['total' => 10, 'subtotal' => 10]);
    Order::factory()->for($customer)->recycle(test()->user)->cancelled()->create(['total' => 20, 'subtotal' => 20]);
    Order::factory()->for($customer)->recycle(test()->user)->ready()->create(['total' => 15, 'subtotal' => 15]);

    expect($customer->order_count)->toBe(2);
});

test('average order value is correct', function () {
    $customer = Customer::factory()->create();
    Order::factory()->for($customer)->recycle(test()->user)->delivered()->create(['total' => 40, 'subtotal' => 40]);
    Order::factory()->for($customer)->recycle(test()->user)->delivered()->create(['total' => 60, 'subtotal' => 60]);

    expect($customer->average_order_value)->toBe(50.00);
});

test('average order value is zero with no orders', function () {
    $customer = Customer::factory()->create();

    expect($customer->average_order_value)->toBe(0.0);
});

test('days since last order returns correct number', function () {
    $customer = Customer::factory()->create();
    $order = Order::factory()->for($customer)->recycle(test()->user)->delivered()->create(['total' => 10, 'subtotal' => 10]);
    Order::query()->where('id', $order->id)->update(['created_at' => now()->subDays(15)]);

    expect($customer->days_since_last_order)->toBe(15);
});

test('days since last order is null with no orders', function () {
    $customer = Customer::factory()->create();

    expect($customer->days_since_last_order)->toBeNull();
});

test('is at risk when 30 plus days inactive', function () {
    $customer = Customer::factory()->create();
    $order = Order::factory()->for($customer)->recycle(test()->user)->delivered()->create(['total' => 10, 'subtotal' => 10]);
    Order::query()->where('id', $order->id)->update(['created_at' => now()->subDays(35)]);

    expect($customer->is_at_risk)->toBeTrue();
});

test('is not at risk for recent customers', function () {
    $customer = Customer::factory()->create();
    $order = Order::factory()->for($customer)->recycle(test()->user)->delivered()->create(['total' => 10, 'subtotal' => 10]);
    Order::query()->where('id', $order->id)->update(['created_at' => now()->subDays(5)]);

    expect($customer->is_at_risk)->toBeFalse();
});

test('is not at risk with no orders', function () {
    $customer = Customer::factory()->create();

    expect($customer->is_at_risk)->toBeFalse();
});

test('last order date returns most recent', function () {
    $customer = Customer::factory()->create();
    $order1 = Order::factory()->for($customer)->recycle(test()->user)->delivered()->create(['total' => 10, 'subtotal' => 10]);
    Order::query()->where('id', $order1->id)->update(['created_at' => now()->subDays(10)]);
    $order2 = Order::factory()->for($customer)->recycle(test()->user)->delivered()->create(['total' => 20, 'subtotal' => 20]);
    Order::query()->where('id', $order2->id)->update(['created_at' => now()->subDays(2)]);

    expect($customer->last_order_date->toDateString())->toBe(now()->subDays(2)->toDateString());
});
