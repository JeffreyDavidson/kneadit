<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('reorder returns items from previous order', function () {
    $product = Product::factory()->create(['name' => 'Sourdough']);
    $order = Order::factory()->create();
    OrderItem::query()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price' => 8.50,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson("/order/reorder/{$order->order_number}");

    $response->assertOk()
        ->assertJsonCount(1, 'items')
        ->assertJsonPath('items.0.product_name', 'Sourdough')
        ->assertJsonPath('items.0.quantity', 2);
});
