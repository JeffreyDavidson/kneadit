<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Category;
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
    config(['database.connections.central' => config('database.connections.sqlite')]);
    $tenantMigrationPath = database_path('migrations/tenant');
    if (is_dir($tenantMigrationPath)) {
        test()->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
    }
    test()->user = User::query()->create(['name' => 'Test', 'email' => 'test@test.com', 'password' => bcrypt('password')]);
    test()->customer = Customer::query()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
});

function makeOrder(array $attrs = []): Order
{
    return Order::query()->create(array_merge([
        'customer_id' => test()->customer->id,
        'user_id' => test()->user->id,
        'status' => OrderStatus::Pending,
        'payment_status' => PaymentStatus::Unpaid,
        'subtotal' => 10.00,
        'total' => 10.00,
        'delivery_date' => now()->addDay(),
        'delivery_time' => '10:00',
    ], $attrs));
}

test('order has customer relationship', function () {
    $order = makeOrder();

    expect($order->customer)->toBeInstanceOf(Customer::class);
    expect($order->customer->id)->toBe(test()->customer->id);
});

test('order has items relationship', function () {
    $category = Category::query()->create(['name' => 'Bread', 'slug' => 'bread']);
    $product = Product::query()->create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 5.00, 'category_id' => $category->id, 'is_active' => true]);
    $order = makeOrder();

    OrderItem::query()->create(['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => 2, 'unit_price' => 5.00]);

    $order->refresh();

    expect($order->orderItems)->toHaveCount(1);
    expect($order->orderItems->first()->quantity)->toBe(2);
});

test('order has messages relationship', function () {
    $order = makeOrder();
    OrderMessage::query()->create(['order_id' => $order->id, 'message' => 'Hello', 'sender_type' => 'baker', 'sender_name' => 'Baker']);

    expect($order->messages)->toHaveCount(1);
});

test('order number is auto generated', function () {
    $order = makeOrder();

    expect($order->order_number)->toStartWith('ORD-');
});

test('order total is cast to decimal', function () {
    $order = makeOrder(['subtotal' => 25.50, 'delivery_fee' => 5.00, 'discount_amount' => 2.50, 'total' => 28.00]);
    $order->refresh();

    expect($order->total)->toBe('28.00');
    expect($order->delivery_fee)->toBe('5.00');
    expect($order->discount_amount)->toBe('2.50');
});

test('order status transitions', function () {
    Mail::fake();
    $order = makeOrder();

    foreach ([OrderStatus::Confirmed, OrderStatus::Baking, OrderStatus::Ready, OrderStatus::Delivered] as $status) {
        $order->update(['status' => $status]);
        expect($order->fresh()->status)->toBe($status);
    }
});

test('order can be cancelled', function () {
    Mail::fake();
    $order = makeOrder();
    $order->update(['status' => OrderStatus::Cancelled]);

    expect($order->fresh()->status)->toBe(OrderStatus::Cancelled);
});

test('order belongs to user', function () {
    $order = makeOrder();

    expect($order->user)->toBeInstanceOf(User::class);
});

test('order item total price attribute', function () {
    $category = Category::query()->create(['name' => 'Bread', 'slug' => 'bread']);
    $product = Product::query()->create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 5.00, 'category_id' => $category->id, 'is_active' => true]);
    $order = makeOrder(['subtotal' => 15.00, 'total' => 15.00]);
    $item = OrderItem::query()->create(['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => 3, 'unit_price' => 5.00]);

    expect($item->total_price)->toBe(15.00);
});
