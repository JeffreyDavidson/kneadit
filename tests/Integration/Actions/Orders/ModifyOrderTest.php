<?php

use App\Actions\Orders\ModifyOrder;
use App\Events\Orders\OrderModified;
use App\Exceptions\Orders\OrderNotModifiableException;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    settings(['order_modification_window_minutes' => 30]);
});

test('updates quantities and recalculates totals', function () {
    Event::fake();

    $product = Product::factory()->create(['price' => 10.00]);
    $order = Order::factory()->pending()->unpaid()->create([
        'subtotal' => 20.00,
        'total' => 20.00,
    ]);
    $item = OrderItem::factory()->for($order)->create([
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price' => 10.00,
    ]);

    resolve(ModifyOrder::class)($order, [
        ['order_item_id' => $item->id, 'quantity' => 5],
    ]);

    $order->refresh();
    expect($order->orderItems()->first()->quantity)->toBe(5);
    expect($order->subtotal->dollars())->toBe(50.00);
    expect($order->total->dollars())->toBe(50.00);
    Event::assertDispatched(OrderModified::class);
});

test('removes item when quantity is set to zero', function () {
    $product = Product::factory()->create(['price' => 10.00]);
    $order = Order::factory()->pending()->unpaid()->create();
    $keep = OrderItem::factory()->for($order)->create(['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 10.00]);
    $remove = OrderItem::factory()->for($order)->create(['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 10.00]);

    resolve(ModifyOrder::class)($order, [
        ['order_item_id' => $keep->id, 'quantity' => 1],
        ['order_item_id' => $remove->id, 'quantity' => 0],
    ]);

    $order->refresh();
    expect($order->orderItems)->toHaveCount(1);
    expect($order->subtotal->dollars())->toBe(10.00);
});

test('updates tip when provided', function () {
    $product = Product::factory()->create(['price' => 10.00]);
    $order = Order::factory()->pending()->unpaid()->create();
    $item = OrderItem::factory()->for($order)->create(['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 10.00]);

    resolve(ModifyOrder::class)($order, [
        ['order_item_id' => $item->id, 'quantity' => 2],
    ], tipAmount: 4.50);

    $order->refresh();
    expect($order->tip_amount->dollars())->toBe(4.50);
    expect($order->total->dollars())->toBe(24.50);
});

test('throws when order is not in pending status', function () {
    $order = Order::factory()->confirmed()->unpaid()->create();
    $item = OrderItem::factory()->for($order)->create(['quantity' => 1, 'unit_price' => 10.00]);

    expect(fn () => resolve(ModifyOrder::class)($order, [
        ['order_item_id' => $item->id, 'quantity' => 2],
    ]))->toThrow(OrderNotModifiableException::class);
});

test('throws when order is already paid', function () {
    $order = Order::factory()->pending()->paid()->create();
    $item = OrderItem::factory()->for($order)->create(['quantity' => 1, 'unit_price' => 10.00]);

    expect(fn () => resolve(ModifyOrder::class)($order, [
        ['order_item_id' => $item->id, 'quantity' => 2],
    ]))->toThrow(OrderNotModifiableException::class);
});

test('throws when modification window has expired', function () {
    settings(['order_modification_window_minutes' => 5]);

    $order = Order::factory()->pending()->unpaid()->create([
        'created_at' => now()->subMinutes(10),
    ]);
    $item = OrderItem::factory()->for($order)->create(['quantity' => 1, 'unit_price' => 10.00]);

    expect(fn () => resolve(ModifyOrder::class)($order, [
        ['order_item_id' => $item->id, 'quantity' => 2],
    ]))->toThrow(OrderNotModifiableException::class);
});

test('throws when feature is disabled (window = 0)', function () {
    settings(['order_modification_window_minutes' => 0]);

    $order = Order::factory()->pending()->unpaid()->create();
    $item = OrderItem::factory()->for($order)->create(['quantity' => 1, 'unit_price' => 10.00]);

    expect(fn () => resolve(ModifyOrder::class)($order, [
        ['order_item_id' => $item->id, 'quantity' => 2],
    ]))->toThrow(OrderNotModifiableException::class);
});

test('rolls back when modification would leave order with no items', function () {
    $order = Order::factory()->pending()->unpaid()->create();
    $only = OrderItem::factory()->for($order)->create(['quantity' => 1, 'unit_price' => 10.00]);

    expect(fn () => resolve(ModifyOrder::class)($order, [
        ['order_item_id' => $only->id, 'quantity' => 0],
    ]))->toThrow(OrderNotModifiableException::class);

    expect($order->fresh()->orderItems()->count())->toBe(1);
});
