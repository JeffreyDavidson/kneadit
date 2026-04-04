<?php

use App\Actions\Orders\MarkOrderPaid;
use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentMethod;
use App\Enums\Orders\PaymentStatus;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();

    Mail::fake();
    test()->user = User::factory()->owner()->create();
    test()->customer = Customer::factory()->create();
});

test('marks order as paid', function () {
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->create(['payment_method' => PaymentMethod::Stripe]);

    app(MarkOrderPaid::class)($order);

    expect($order->refresh()->payment_status)->toBe(PaymentStatus::Paid);
});

test('auto-confirms pending stripe order', function () {
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->create(['payment_method' => PaymentMethod::Stripe]);

    app(MarkOrderPaid::class)($order);

    expect($order->refresh())
        ->payment_status->toBe(PaymentStatus::Paid)
        ->status->toBe(OrderStatus::Confirmed);
});

test('auto-confirms pending paypal order', function () {
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->create(['payment_method' => PaymentMethod::PayPal]);

    app(MarkOrderPaid::class)($order);

    expect($order->refresh())
        ->payment_status->toBe(PaymentStatus::Paid)
        ->status->toBe(OrderStatus::Confirmed);
});

test('does not auto-confirm cash order', function () {
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->create(['payment_method' => PaymentMethod::Cash]);

    app(MarkOrderPaid::class)($order);

    expect($order->refresh())
        ->payment_status->toBe(PaymentStatus::Paid)
        ->status->toBe(OrderStatus::Pending);
});

test('does not auto-confirm other payment method order', function () {
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->create(['payment_method' => PaymentMethod::Other]);

    app(MarkOrderPaid::class)($order);

    expect($order->refresh())
        ->payment_status->toBe(PaymentStatus::Paid)
        ->status->toBe(OrderStatus::Pending);
});

test('is idempotent when called twice', function () {
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->create(['payment_method' => PaymentMethod::Stripe]);

    app(MarkOrderPaid::class)($order);
    app(MarkOrderPaid::class)($order);

    expect($order->refresh())
        ->payment_status->toBe(PaymentStatus::Paid)
        ->status->toBe(OrderStatus::Confirmed);
});

test('skips status transition when order is already confirmed', function () {
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->confirmed()
        ->create(['payment_method' => PaymentMethod::Stripe]);

    app(MarkOrderPaid::class)($order);

    expect($order->refresh())
        ->payment_status->toBe(PaymentStatus::Paid)
        ->status->toBe(OrderStatus::Confirmed);
});

test('skips status transition when order is already beyond confirmed', function () {
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->baking()
        ->create(['payment_method' => PaymentMethod::Stripe]);

    app(MarkOrderPaid::class)($order);

    expect($order->refresh())
        ->payment_status->toBe(PaymentStatus::Paid)
        ->status->toBe(OrderStatus::Baking);
});
