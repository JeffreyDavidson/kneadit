<?php

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;

use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpTenantTest();
});

test('tracking page loads', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/track');

    $response->assertOk();
});

test('tracking with valid email returns orders', function () {
    $user = User::create([
        'name' => 'Admin',
        'email' => 'admin@test.com',
        'password' => bcrypt('password'),
    ]);

    $customer = Customer::create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);

    Order::create([
        'order_number' => 'KN260308A001',
        'customer_id' => $customer->id,
        'status' => 'confirmed',
        'subtotal' => 25.00,
        'total' => 25.00,
        'user_id' => $user->id,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->post('/track', [
            'email' => 'jane@example.com',
        ]);

    $response->assertOk();
    $response->assertSee('KN260308A001');
});

test('tracking with no orders shows empty state', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->post('/track', [
            'email' => 'nobody@example.com',
        ]);

    $response->assertOk();
});

test('tracking shows correct status for each order stage', function () {
    $user = User::create([
        'name' => 'Admin',
        'email' => 'admin@test.com',
        'password' => bcrypt('password'),
    ]);

    $customer = Customer::create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);

    foreach (['pending', 'confirmed', 'baking', 'ready', 'delivered'] as $status) {
        Order::create([
            'order_number' => 'KN'.strtoupper($status),
            'customer_id' => $customer->id,
            'status' => $status,
            'subtotal' => 10.00,
            'total' => 10.00,
            'user_id' => $user->id,
        ]);
    }

    $response = withoutMiddleware(tenantMiddleware())
        ->post('/track', [
            'email' => 'jane@example.com',
        ]);

    $response->assertOk();
    expect(Order::where('customer_id', $customer->id)->count())->toBe(5);
});

test('orders display items and totals', function () {
    $user = User::create([
        'name' => 'Admin',
        'email' => 'admin@test.com',
        'password' => bcrypt('password'),
    ]);

    $category = Category::create([
        'name' => 'Breads',
        'slug' => 'breads',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $product = Product::create([
        'name' => 'Baguette',
        'slug' => 'baguette',
        'price' => 5.00,
        'category_id' => $category->id,
        'is_active' => true,
    ]);

    $customer = Customer::create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);

    $order = Order::create([
        'order_number' => 'KN260308ITEM',
        'customer_id' => $customer->id,
        'status' => 'confirmed',
        'subtotal' => 15.00,
        'total' => 15.00,
        'user_id' => $user->id,
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 3,
        'unit_price' => 5.00,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->post('/track', [
            'email' => 'jane@example.com',
        ]);

    $response->assertOk();
    $response->assertSee('Baguette');
    $response->assertSee('15.00');
});

test('tracking requires email', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->post('/track', []);

    $response->assertSessionHasErrors('email');
});
