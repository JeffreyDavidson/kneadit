<?php

use App\Enums\CouponType;
use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpTenantTest();

    $this->category = Category::factory()->create([
        'name' => 'Breads',
        'slug' => 'breads',
    ]);

    $this->product = Product::factory()->for($this->category)->create([
        'name' => 'Sourdough Loaf',
        'slug' => 'sourdough-loaf',
        'price' => 12.50,
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
            'delivery_type' => DeliveryType::Pickup->value,
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
            'delivery_type' => DeliveryType::Pickup->value,
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
            'delivery_type' => DeliveryType::Pickup->value,
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
            'delivery_type' => DeliveryType::Pickup->value,
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
            'delivery_type' => DeliveryType::Pickup->value,
            'delivery_date' => now()->addDays(3)->toDateString(),
            'items' => [],
        ]);

    $response->assertSessionHasErrors('items');
});

test('coupon application works for valid coupon', function () {
    $coupon = Coupon::factory()->create([
        'code' => 'SAVE10',
        'type' => CouponType::Percentage,
        'value' => 10.00,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson(route('coupon.apply', [], false), [
            'code' => 'SAVE10',
            'subtotal' => 50.00,
        ]);

    $response->assertOk();
    $response->assertJsonStructure(['data' => ['coupon_id', 'discount_amount']]);
});

test('invalid coupon returns error', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->postJson(route('coupon.apply', [], false), [
            'code' => 'FAKECODE',
            'subtotal' => 50.00,
        ]);

    $response->assertUnprocessable();
    $response->assertJsonStructure(['message']);
});

test('capacity check endpoint works', function () {
    $date = now()->addDays(5)->toDateString();

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson(route('capacity.check', ['date' => $date], false));

    $response->assertOk();
    $response->assertJsonStructure(['data' => ['available', 'remaining', 'max_orders']]);
});

test('order confirmation page shows after successful order', function () {
    $user = User::factory()->owner()->create();
    $customer = Customer::factory()->create();

    $order = Order::factory()
        ->for($customer)
        ->recycle($user)
        ->create([
            'order_number' => 'KN260308TEST',
            'status' => OrderStatus::Pending,
            'subtotal' => 25.00,
            'total' => 25.00,
        ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.confirmation', ['order' => $order->order_number], false));

    $response->assertOk();
});
