<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['database.connections.central' => config('database.connections.sqlite')]);
    $tenantMigrationPath = database_path('migrations/tenant');
    if (is_dir($tenantMigrationPath)) {
        test()->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
    }
    test()->user = User::create(['name' => 'Test', 'email' => 'test@test.com', 'password' => bcrypt('password')]);
});

test('lifetime value sums non cancelled orders', function () {
    $customer = Customer::create(['name' => 'Alice', 'email' => 'alice@test.com']);
    Order::create(['user_id' => test()->user->id, 'customer_id' => $customer->id, 'status' => 'delivered', 'total' => 50.00, 'subtotal' => 50]);
    Order::create(['user_id' => test()->user->id, 'customer_id' => $customer->id, 'status' => 'confirmed', 'total' => 30.00, 'subtotal' => 30]);

    expect($customer->lifetime_value)->toBe(80.00);
});

test('lifetime value excludes cancelled orders', function () {
    $customer = Customer::create(['name' => 'Bob', 'email' => 'bob@test.com']);
    Order::create(['user_id' => test()->user->id, 'customer_id' => $customer->id, 'status' => 'delivered', 'total' => 50.00, 'subtotal' => 50]);
    Order::create(['user_id' => test()->user->id, 'customer_id' => $customer->id, 'status' => 'cancelled', 'total' => 30.00, 'subtotal' => 30]);

    expect($customer->lifetime_value)->toBe(50.00);
});

test('order count counts non cancelled orders', function () {
    $customer = Customer::create(['name' => 'Carol', 'email' => 'carol@test.com']);
    Order::create(['user_id' => test()->user->id, 'customer_id' => $customer->id, 'status' => 'delivered', 'total' => 10, 'subtotal' => 10]);
    Order::create(['user_id' => test()->user->id, 'customer_id' => $customer->id, 'status' => 'cancelled', 'total' => 20, 'subtotal' => 20]);
    Order::create(['user_id' => test()->user->id, 'customer_id' => $customer->id, 'status' => 'ready', 'total' => 15, 'subtotal' => 15]);

    expect($customer->order_count)->toBe(2);
});

test('average order value is correct', function () {
    $customer = Customer::create(['name' => 'Dave', 'email' => 'dave@test.com']);
    Order::create(['user_id' => test()->user->id, 'customer_id' => $customer->id, 'status' => 'delivered', 'total' => 40, 'subtotal' => 40]);
    Order::create(['user_id' => test()->user->id, 'customer_id' => $customer->id, 'status' => 'delivered', 'total' => 60, 'subtotal' => 60]);

    expect($customer->average_order_value)->toBe(50.00);
});

test('average order value is zero with no orders', function () {
    $customer = Customer::create(['name' => 'Eve', 'email' => 'eve@test.com']);

    expect($customer->average_order_value)->toBe(0.0);
});

test('days since last order returns correct number', function () {
    $customer = Customer::create(['name' => 'Frank', 'email' => 'frank@test.com']);
    $order = Order::create(['user_id' => test()->user->id,
        'customer_id' => $customer->id,
        'status' => 'delivered',
        'total' => 10,
        'subtotal' => 10,
    ]);
    Order::where('id', $order->id)->update(['created_at' => now()->subDays(15)]);

    expect($customer->days_since_last_order)->toBe(15);
});

test('days since last order is null with no orders', function () {
    $customer = Customer::create(['name' => 'Grace', 'email' => 'grace@test.com']);

    expect($customer->days_since_last_order)->toBeNull();
});

test('is at risk when 30 plus days inactive', function () {
    $customer = Customer::create(['name' => 'Hank', 'email' => 'hank@test.com']);
    $order = Order::create(['user_id' => test()->user->id,
        'customer_id' => $customer->id,
        'status' => 'delivered',
        'total' => 10,
        'subtotal' => 10,
    ]);
    Order::where('id', $order->id)->update(['created_at' => now()->subDays(35)]);

    expect($customer->is_at_risk)->toBeTrue();
});

test('is not at risk for recent customers', function () {
    $customer = Customer::create(['name' => 'Ivy', 'email' => 'ivy@test.com']);
    $order = Order::create(['user_id' => test()->user->id,
        'customer_id' => $customer->id,
        'status' => 'delivered',
        'total' => 10,
        'subtotal' => 10,
    ]);
    Order::where('id', $order->id)->update(['created_at' => now()->subDays(5)]);

    expect($customer->is_at_risk)->toBeFalse();
});

test('is not at risk with no orders', function () {
    $customer = Customer::create(['name' => 'Jack', 'email' => 'jack@test.com']);

    expect($customer->is_at_risk)->toBeFalse();
});

test('last order date returns most recent', function () {
    $customer = Customer::create(['name' => 'Kate', 'email' => 'kate@test.com']);
    $order1 = Order::create(['user_id' => test()->user->id,
        'customer_id' => $customer->id,
        'status' => 'delivered',
        'total' => 10,
        'subtotal' => 10,
    ]);
    Order::where('id', $order1->id)->update(['created_at' => now()->subDays(10)]);
    $order2 = Order::create(['user_id' => test()->user->id,
        'customer_id' => $customer->id,
        'status' => 'delivered',
        'total' => 20,
        'subtotal' => 20,
    ]);
    Order::where('id', $order2->id)->update(['created_at' => now()->subDays(2)]);

    expect($customer->last_order_date->toDateString())->toBe(now()->subDays(2)->toDateString());
});
