<?php

use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('reorder returns items from previous order', function () {
    $product = Product::factory()->create();
    $order = Order::factory()->create();
    OrderItem::factory()->recycle($order, $product)->create(['quantity' => 2]);

    $response = withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([$order]))
        ->getJson(route('order.reorder', ['order' => $order], false));

    $response->assertOk()
        ->assertJsonCount(1, 'data.items')
        ->assertJsonPath('data.items.0.product_id', $product->id)
        ->assertJsonPath('data.items.0.product_name', $product->name)
        ->assertJsonPath('data.items.0.quantity', 2);
});

test('reorder uses the current product price and drops products that can no longer be ordered', function () {
    $product = Product::factory()->create(['price' => 12.00]);
    $retired = Product::factory()->create(['name' => 'Retired Rye', 'is_active' => false]);
    $order = Order::factory()->create();
    OrderItem::factory()->recycle($order, $product)->create(['quantity' => 2, 'unit_price' => 8.00]);
    OrderItem::factory()->recycle($order, $retired)->create(['quantity' => 1, 'unit_price' => 6.00]);

    $response = withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([$order]))
        ->getJson(route('order.reorder', ['order' => $order], false));

    $response->assertOk()
        ->assertJsonCount(1, 'data.items')
        ->assertJsonPath('data.items.0.product_id', $product->id)
        ->assertJsonPath('data.items.0.price', 12)
        ->assertJsonPath('data.removed_items', ['Retired Rye']);
});

test('reorder names a deleted product from the name saved on the order line', function () {
    $order = Order::factory()->create();
    OrderItem::factory()->recycle($order)->create(['product_id' => null, 'name' => 'Old Loaf', 'quantity' => 1]);

    $response = withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([$order]))
        ->getJson(route('order.reorder', ['order' => $order], false));

    $response->assertOk()
        ->assertJsonCount(0, 'data.items')
        ->assertJsonPath('data.removed_items', ['Old Loaf']);
});
