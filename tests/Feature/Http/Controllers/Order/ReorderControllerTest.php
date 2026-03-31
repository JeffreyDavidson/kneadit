<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\withoutMiddleware;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('reorder returns items from previous order', function () {
    $product = Product::factory()->create();
    $order = Order::factory()->create();
    OrderItem::factory()->recycle($order, $product)->create(['quantity' => 2]);

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson(route('order.reorder', ['order' => $order], false));

    $response->assertOk()
        ->assertJsonCount(1, 'data.items')
        ->assertJsonPath('data.items.0.product_id', $product->id)
        ->assertJsonPath('data.items.0.quantity', 2);
});
