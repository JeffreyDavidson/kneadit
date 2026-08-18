<?php

use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Services\PayPal\InvoicePayloadBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('builds payload with correct structure for a basic order', function () {
    $customer = Customer::factory()->withAddress()->create(['name' => 'Jane Doe']);
    $order = Order::factory()->recycle($customer)->withItems(2)->create();

    $builder = resolve(InvoicePayloadBuilder::class);
    $payload = $builder->build($order);

    expect($payload)
        ->toHaveKeys(['detail', 'invoicer', 'primary_recipients', 'items', 'configuration', 'amount'])
        ->and($payload['detail']['invoice_number'])->toBe($order->order_number)
        ->and($payload['detail']['reference'])->toBe("Order #{$order->order_number}")
        ->and($payload['invoicer']['address']['country_code'])->toBe('US')
        ->and($payload['primary_recipients'][0]['billing_info']['name']['given_name'])->toBe('Jane')
        ->and($payload['primary_recipients'][0]['billing_info']['name']['surname'])->toBe('Doe')
        ->and($payload['items'])->toHaveCount(2)
        ->and($payload['configuration']['allow_tip'])->toBeFalse();
});

test('includes delivery fee as line item when delivery fee is positive', function () {
    $order = Order::factory()->withItems(1)->create([
        'delivery_fee' => 5.00,
        'subtotal' => 20.00,
    ]);

    $builder = resolve(InvoicePayloadBuilder::class);
    $payload = $builder->build($order);

    $lastItem = end($payload['items']);

    expect($payload['items'])->toHaveCount(2)
        ->and($lastItem['name'])->toBe('Delivery Fee')
        ->and($lastItem['unit_amount']['value'])->toBe('5.00');
});

test('excludes delivery fee line item when delivery fee is zero', function () {
    $order = Order::factory()->withItems(1)->create([
        'delivery_fee' => 0,
    ]);

    $builder = resolve(InvoicePayloadBuilder::class);
    $payload = $builder->build($order);

    expect($payload['items'])->toHaveCount(1);
});

test('includes discount breakdown when discount amount is positive', function () {
    $order = Order::factory()->create([
        'discount_amount' => 2.50,
        'subtotal' => 20.00,
    ]);

    $builder = resolve(InvoicePayloadBuilder::class);
    $payload = $builder->build($order);

    expect($payload['amount']['breakdown'])->toHaveKey('discount');
});

test('excludes discount breakdown when discount amount is zero', function () {
    $order = Order::factory()->create([
        'discount_amount' => 0,
        'subtotal' => 20.00,
    ]);

    $builder = resolve(InvoicePayloadBuilder::class);
    $payload = $builder->build($order);

    expect($payload['amount']['breakdown'])->not->toHaveKey('discount');
});

test('parses single-word customer name correctly', function () {
    $customer = Customer::factory()->create(['name' => 'Madonna']);
    $order = Order::factory()->recycle($customer)->create();

    $builder = resolve(InvoicePayloadBuilder::class);
    $payload = $builder->build($order);

    $name = $payload['primary_recipients'][0]['billing_info']['name'];

    expect($name['given_name'])->toBe('Madonna')
        ->and($name['surname'])->toBeEmpty();
});

test('parses multi-word customer name correctly', function () {
    $customer = Customer::factory()->create(['name' => 'Mary Jane Watson']);
    $order = Order::factory()->recycle($customer)->create();

    $builder = resolve(InvoicePayloadBuilder::class);
    $payload = $builder->build($order);

    $name = $payload['primary_recipients'][0]['billing_info']['name'];

    expect($name['given_name'])->toBe('Mary')
        ->and($name['surname'])->toBe('Jane Watson');
});

test('handles empty customer name gracefully', function () {
    $customer = Customer::factory()->create(['name' => '']);
    $order = Order::factory()->recycle($customer)->create();

    $builder = resolve(InvoicePayloadBuilder::class);
    $payload = $builder->build($order);

    $name = $payload['primary_recipients'][0]['billing_info']['name'];

    expect($name['given_name'])->toBeEmpty()
        ->and($name['surname'])->toBeEmpty();
});

test('strips non-digit characters from customer phone number', function () {
    $customer = Customer::factory()->create(['phone' => '(555) 123-4567']);
    $order = Order::factory()->recycle($customer)->create();

    $builder = resolve(InvoicePayloadBuilder::class);
    $payload = $builder->build($order);

    $phones = $payload['primary_recipients'][0]['billing_info']['phones'];

    expect($phones)->toHaveCount(1)
        ->and($phones[0]['national_number'])->toBe('5551234567');
});

test('returns empty phone array when customer has no phone', function () {
    $customer = Customer::factory()->create(['phone' => null]);
    $order = Order::factory()->recycle($customer)->create();

    $builder = resolve(InvoicePayloadBuilder::class);
    $payload = $builder->build($order);

    $phones = $payload['primary_recipients'][0]['billing_info']['phones'];

    expect($phones)->toBeEmpty();
});

test('formats item unit prices with two decimal places', function () {
    $order = Order::factory()->create(['subtotal' => 10.00]);
    OrderItem::factory()->recycle($order)->create(['unit_price' => 10, 'quantity' => 1]);

    $builder = resolve(InvoicePayloadBuilder::class);
    $payload = $builder->build($order);

    expect($payload['items'][0]['unit_amount']['value'])->toBe('10.00');
});
