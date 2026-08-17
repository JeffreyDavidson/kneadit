<?php

use App\Models\Customers\Customer;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Services\Stripe\StripeSessionPayloadBuilder;
use App\ValueObjects\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    config(['cashier.currency' => 'usd']);
    test()->builder = new StripeSessionPayloadBuilder;
});

test('lineItems builds one entry per order item with cents amounts', function () {
    $order = Order::factory()->create();
    $product = Product::factory()->create(['name' => 'Sourdough']);
    OrderItem::factory()->for($order)->for($product)->create([
        'quantity' => 2,
        'unit_price' => Money::fromDollars(7.50),
        'special_instructions' => 'Sliced',
    ]);

    $items = test()->builder->lineItems($order->refresh());

    expect($items)->toHaveCount(1)
        ->and($items[0]['quantity'])->toBe(2)
        ->and($items[0]['price_data']['unit_amount'])->toBe(750)
        ->and($items[0]['price_data']['product_data']['name'])->toBe('Sourdough')
        ->and($items[0]['price_data']['product_data']['description'])->toBe('Sliced');
});

test('lineItems appends a delivery fee entry when delivery_fee is positive', function () {
    $order = Order::factory()->create([
        'delivery_fee' => Money::fromDollars(5.00),
    ]);
    OrderItem::factory()->for($order)->create([
        'quantity' => 1,
        'unit_price' => Money::fromDollars(10.00),
    ]);

    $items = test()->builder->lineItems($order->refresh());

    expect($items)->toHaveCount(2)
        ->and($items[1]['price_data']['product_data']['name'])->toBe('Delivery Fee')
        ->and($items[1]['price_data']['unit_amount'])->toBe(500)
        ->and($items[1]['quantity'])->toBe(1);
});

test('lineItems omits delivery fee when zero', function () {
    $order = Order::factory()->create(['delivery_fee' => Money::zero()]);
    OrderItem::factory()->for($order)->create([
        'quantity' => 1,
        'unit_price' => Money::fromDollars(10.00),
    ]);

    expect(test()->builder->lineItems($order->refresh()))->toHaveCount(1);
});

test('build assembles full session params with metadata', function () {
    $customer = Customer::factory()->create(['email' => 'baker@example.com']);
    $order = Order::factory()->for($customer)->create([
        'order_number' => 'ORD-001',
    ]);
    OrderItem::factory()->for($order)->create([
        'quantity' => 1,
        'unit_price' => Money::fromDollars(15.00),
    ]);

    $params = test()->builder->build($order->refresh(), 'tenant-abc', 'https://success', 'https://cancel');

    expect($params)
        ->mode->toBe('payment')
        ->success_url->toBe('https://success')
        ->cancel_url->toBe('https://cancel')
        ->customer_email->toBe('baker@example.com')
        ->and($params['metadata'])->toHaveKey('order_number', 'ORD-001')
        ->toHaveKey('order_id', $order->id)
        ->toHaveKey('tenant_id', 'tenant-abc')
        ->and($params['payment_intent_data']['metadata'])->toHaveKey('order_number', 'ORD-001')
        ->toHaveKey('tenant_id', 'tenant-abc')
        ->and($params)->not->toHaveKey('discounts');
});

test('build includes discounts key when discounts array is provided', function () {
    $order = Order::factory()->create();
    OrderItem::factory()->for($order)->create([
        'quantity' => 1,
        'unit_price' => Money::fromDollars(10.00),
    ]);

    $params = test()->builder->build(
        $order->refresh(),
        'tenant-abc',
        'https://success',
        'https://cancel',
        [['coupon' => 'coupon_123']],
    );

    expect($params['discounts'])->toBe([['coupon' => 'coupon_123']]);
});
