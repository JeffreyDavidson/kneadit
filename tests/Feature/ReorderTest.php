<?php

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;

beforeEach(function () {
    setUpTenantTest();
});

function createOrderWithItems(string $productName = 'Sourdough', float $price = 8.50): Order
{
    $user = User::create(['name' => 'Baker', 'email' => 'baker@test.com', 'password' => bcrypt('pass')]);
    $customer = Customer::create(['name' => 'Test', 'email' => 'test@example.com']);
    $category = Category::create(['name' => $productName.' Cat', 'slug' => 'cat-'.uniqid()]);
    $product = Product::create(['name' => $productName, 'slug' => Str::slug($productName).'-'.uniqid(), 'price' => $price, 'category_id' => $category->id, 'is_active' => true]);

    $order = Order::create([
        'order_number' => 'ORD-RE-'.uniqid(),
        'customer_id' => $customer->id,
        'user_id' => $user->id,
        'status' => 'delivered',
        'subtotal' => $price * 2,
        'total' => $price * 2,
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price' => $price,
    ]);

    return $order;
}

test('reorder endpoint returns order items as json', function () {
    $order = createOrderWithItems();

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson("/order/reorder/{$order->order_number}");

    $response->assertOk();
    $response->assertJsonStructure(['items' => [['product_id', 'product_name', 'price', 'quantity']]]);
});

test('reorder data includes product names and prices', function () {
    $order = createOrderWithItems('Croissant', 4.00);

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson("/order/reorder/{$order->order_number}");

    $response->assertJsonPath('items.0.product_name', 'Croissant');
    $response->assertJsonPath('items.0.price', '4.00');
});

test('reorder returns 404 for nonexistent order', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->getJson('/order/reorder/99999');

    $response->assertNotFound();
});
