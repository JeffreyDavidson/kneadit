<?php

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpTenantTest();

    $this->category = Category::create([
        'name' => 'Breads',
        'slug' => 'breads',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $this->product = Product::create([
        'name' => 'Sourdough Loaf',
        'slug' => 'sourdough-loaf',
        'price' => 12.50,
        'category_id' => $this->category->id,
        'is_active' => true,
    ]);
});

test('order page loads', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.create', [], false));

    $response->assertOk();
});

test('order validation passes with valid data', function () {
    $deliveryDate = now()->addDays(3)->toDateString();

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('order.store', [], false), [
            'customer_name' => 'John Baker',
            'customer_email' => 'john@example.com',
            'customer_phone' => '555-1234',
            'delivery_type' => 'pickup',
            'delivery_date' => $deliveryDate,
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 2],
            ],
        ]);

    $response->assertSessionDoesntHaveErrors(['customer_name', 'customer_email', 'delivery_date']);
});

test('order validation rejects missing customer name', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('order.store', [], false), [
            'customer_email' => 'john@example.com',
            'delivery_type' => 'pickup',
            'delivery_date' => now()->addDays(3)->toDateString(),
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 1],
            ],
        ]);

    $response->assertSessionHasErrors('customer_name');
});

test('order validation rejects missing email', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('order.store', [], false), [
            'customer_name' => 'John Baker',
            'delivery_type' => 'pickup',
            'delivery_date' => now()->addDays(3)->toDateString(),
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 1],
            ],
        ]);

    $response->assertSessionHasErrors('customer_email');
});

test('order validation rejects missing delivery date', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('order.store', [], false), [
            'customer_name' => 'John Baker',
            'customer_email' => 'john@example.com',
            'delivery_type' => 'pickup',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 1],
            ],
        ]);

    $response->assertSessionHasErrors('delivery_date');
});

test('order validation rejects empty cart', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('order.store', [], false), [
            'customer_name' => 'John Baker',
            'customer_email' => 'john@example.com',
            'delivery_type' => 'pickup',
            'delivery_date' => now()->addDays(3)->toDateString(),
            'items' => [],
        ]);

    $response->assertSessionHasErrors('items');
});

test('coupon application works for valid coupon', function () {
    $coupon = Coupon::create([
        'code' => 'SAVE10',
        'type' => 'percentage',
        'value' => 10.00,
        'is_active' => true,
        'starts_at' => now()->subDay(),
        'expires_at' => now()->addMonth(),
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson(route('coupon.apply', [], false), [
            'code' => 'SAVE10',
            'subtotal' => 50.00,
        ]);

    $response->assertOk();
    $response->assertJsonStructure(['success', 'coupon_id', 'discount']);
});

test('invalid coupon returns error', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->postJson(route('coupon.apply', [], false), [
            'code' => 'FAKECODE',
            'subtotal' => 50.00,
        ]);

    $response->assertUnprocessable();
    $response->assertJsonStructure(['error']);
});

test('capacity check endpoint works', function () {
    $date = now()->addDays(5)->toDateString();

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson(route('capacity.check', ['date' => $date], false));

    $response->assertOk();
    $response->assertJsonStructure(['available', 'remaining', 'max_orders']);
});

test('order confirmation page shows after successful order', function () {
    $user = User::create([
        'name' => 'Admin',
        'email' => 'admin@test.com',
        'password' => bcrypt('password'),
    ]);

    $customer = Customer::create([
        'name' => 'John Baker',
        'email' => 'john@example.com',
    ]);

    $order = Order::create([
        'order_number' => 'KN260308TEST',
        'customer_id' => $customer->id,
        'status' => 'pending',
        'subtotal' => 25.00,
        'total' => 25.00,
        'user_id' => $user->id,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.confirmation', ['order' => $order->order_number], false));

    $response->assertOk();
});
