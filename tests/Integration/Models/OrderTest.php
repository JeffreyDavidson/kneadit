<?php

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderMessage;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->user = User::factory()->owner()->create();
    $this->customer = Customer::factory()->create();
});

test('order has customer relationship', function () {
    $order = Order::factory()->for($this->customer)->recycle($this->user)->create();

    expect($order->customer)->toBeInstanceOf(Customer::class)
        ->and($order->customer->id)->toBe($this->customer->id);
});

test('order has items relationship', function () {
    $product = Product::factory()->create();
    $order = Order::factory()->for($this->customer)->recycle($this->user)->create();

    OrderItem::factory()->recycle($order, $product)->create(['quantity' => 2, 'unit_price' => 5.00]);

    expect($order->refresh()->orderItems)->toHaveCount(1)
        ->and($order->orderItems->first()->quantity)->toBe(2);
});

test('order has messages relationship', function () {
    $order = Order::factory()->for($this->customer)->recycle($this->user)->create();

    OrderMessage::factory()->fromBaker()->recycle($order)->create();

    expect($order->messages)->toHaveCount(1);
});

test('order number is auto generated', function () {
    $order = Order::factory()->for($this->customer)->recycle($this->user)->create();

    expect($order->order_number)->toStartWith('ORD-');
});

test('order total is cast to decimal', function () {
    $order = Order::factory()->for($this->customer)->recycle($this->user)->create([
        'subtotal' => 25.50,
        'delivery_fee' => 5.00,
        'discount_amount' => 2.50,
        'total' => 28.00,
    ]);

    expect($order->refresh()->total)->toBe('28.00')
        ->and($order->delivery_fee)->toBe('5.00')
        ->and($order->discount_amount)->toBe('2.50');
});

test('order status transitions', function () {
    Mail::fake();
    $order = Order::factory()->for($this->customer)->recycle($this->user)->create();

    foreach ([OrderStatus::Confirmed, OrderStatus::Baking, OrderStatus::Ready, OrderStatus::Delivered] as $status) {
        $order->update(['status' => $status]);
        expect($order->fresh()->status)->toBe($status);
    }
});

test('order can be cancelled', function () {
    Mail::fake();
    $order = Order::factory()->for($this->customer)->recycle($this->user)->create();
    $order->update(['status' => OrderStatus::Cancelled]);

    expect($order->fresh()->status)->toBe(OrderStatus::Cancelled);
});

test('order belongs to user', function () {
    $order = Order::factory()->for($this->customer)->recycle($this->user)->create();

    expect($order->user)->toBeInstanceOf(User::class);
});

test('order item total price attribute', function () {
    $product = Product::factory()->create();
    $order = Order::factory()->for($this->customer)->recycle($this->user)->create([
        'subtotal' => 15.00,
        'total' => 15.00,
    ]);

    $item = OrderItem::factory()->recycle($order, $product)->create([
        'quantity' => 3,
        'unit_price' => 5.00,
    ]);

    expect($item->total_price)->toBe(15.00);
});
