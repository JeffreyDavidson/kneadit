<?php

use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('reorder returns items from previous order', function () {
    $product = Product::factory()->create(['name' => 'Sourdough']);
    $order = Order::factory()->create();
    OrderItem::factory()->recycle($order, $product)->create([
        'quantity' => 2,
        'unit_price' => 8.50,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson("/order/reorder/{$order->order_number}");

    $response->assertOk()
        ->assertJsonCount(1, 'data.items')
        ->assertJsonPath('data.items.0.product_name', 'Sourdough')
        ->assertJsonPath('data.items.0.quantity', 2);
});
