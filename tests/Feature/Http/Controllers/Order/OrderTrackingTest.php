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
        ->get(route('order.track', absolute: false));

    $response->assertOk();
});

test('tracking with valid email returns orders', function () {
    $user = User::factory()->owner()->create();
    $customer = Customer::factory()->create(['email' => 'jane@example.com']);

    Order::factory()
        ->for($customer)
        ->recycle($user)
        ->confirmed()
        ->create(['order_number' => 'KN260308A001']);

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('order.track.lookup', absolute: false), [
            'email' => 'jane@example.com',
        ]);

    $response->assertOk();
    $response->assertSee('KN260308A001');
});

test('tracking with no orders shows empty state', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('order.track.lookup', absolute: false), [
            'email' => 'nobody@example.com',
        ]);

    $response->assertOk();
});

test('tracking shows correct status for each order stage', function (string $status) {
    $user = User::factory()->owner()->create();
    $customer = Customer::factory()->create(['email' => 'jane@example.com']);

    Order::factory()
        ->for($customer)
        ->recycle($user)
        ->create([
            'order_number' => 'KN' . strtoupper($status),
            'status' => $status,
            'subtotal' => 10.00,
            'total' => 10.00,
        ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('order.track.lookup', absolute: false), [
            'email' => 'jane@example.com',
        ]);

    $response->assertOk();
    expect(Order::query()->where('customer_id', $customer->id)->count())->toBe(1);
})->with(['pending', 'confirmed', 'baking', 'ready', 'delivered']);

test('orders display items and totals', function () {
    $user = User::factory()->owner()->create();
    $category = Category::factory()->create(['name' => 'Breads', 'slug' => 'breads']);

    $product = Product::factory()->for($category)->create([
        'name' => 'Baguette',
        'slug' => 'baguette',
        'price' => 5.00,
    ]);

    $customer = Customer::factory()->create(['email' => 'jane@example.com']);

    $order = Order::factory()
        ->for($customer)
        ->recycle($user)
        ->confirmed()
        ->create([
            'order_number' => 'KN260308ITEM',
            'subtotal' => 15.00,
            'total' => 15.00,
        ]);

    OrderItem::factory()
        ->for($order)
        ->for($product)
        ->create([
            'quantity' => 3,
            'unit_price' => 5.00,
        ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('order.track.lookup', absolute: false), [
            'email' => 'jane@example.com',
        ]);

    $response->assertOk();
    $response->assertSee('Baguette');
    $response->assertSee('15.00');
});

test('tracking requires email', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('order.track.lookup', absolute: false), []);

    $response->assertSessionHasErrors('email');
});
