<?php

use App\DataTransferObjects\Orders\CreateOrderData;
use App\Enums\Orders\DeliveryType;
use App\Models\Inventory\Product;
use App\Pipes\Orders\CalculateOrderTotals;
use App\Pipes\Orders\OrderPipelineData;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('calculates subtotal and total for active products', function () {
    $product = Product::factory()->create(['price' => 10.00, 'is_active' => true]);

    $data = new CreateOrderData(
        customerName: 'Jane',
        customerEmail: 'jane@example.com',
        deliveryDate: now()->addDay()->format('Y-m-d'),
        deliveryType: DeliveryType::Pickup->value,
        items: [['product_id' => $product->id, 'quantity' => 3]],
    );

    $payload = new OrderPipelineData($data);
    $pipe = new CalculateOrderTotals;

    $result = $pipe->handle($payload, fn ($p) => $p);

    expect($result->subtotal)->toBe(30.0)
        ->and($result->total)->toBe(30.0)
        ->and($result->orderItems)->toHaveCount(1)
        ->and($result->cancelled)->toBeFalse();
});

test('skips inactive products', function () {
    $active = Product::factory()->create(['price' => 10.00, 'is_active' => true]);
    $inactive = Product::factory()->create(['price' => 5.00, 'is_active' => false]);

    $data = new CreateOrderData(
        customerName: 'Jane',
        customerEmail: 'jane@example.com',
        deliveryDate: now()->addDay()->format('Y-m-d'),
        deliveryType: DeliveryType::Pickup->value,
        items: [
            ['product_id' => $active->id, 'quantity' => 1],
            ['product_id' => $inactive->id, 'quantity' => 2],
        ],
    );

    $payload = new OrderPipelineData($data);
    $pipe = new CalculateOrderTotals;

    $result = $pipe->handle($payload, fn ($p) => $p);

    expect($result->subtotal)->toBe(10.0)
        ->and($result->orderItems)->toHaveCount(1);
});

test('cancels order when no valid items exist', function () {
    $product = Product::factory()->create(['price' => 5.00, 'is_active' => false]);

    $data = new CreateOrderData(
        customerName: 'Jane',
        customerEmail: 'jane@example.com',
        deliveryDate: now()->addDay()->format('Y-m-d'),
        deliveryType: DeliveryType::Pickup->value,
        items: [['product_id' => $product->id, 'quantity' => 1]],
    );

    $payload = new OrderPipelineData($data);
    $pipe = new CalculateOrderTotals;

    $result = $pipe->handle($payload, fn ($p) => $p);

    expect($result->cancelled)->toBeTrue()
        ->and($result->orderItems)->toBeEmpty();
});

test('adds delivery fee for delivery orders', function () {
    config(['kneadit.delivery_fees' => ['under5' => 5.00, '5to10' => 8.00]]);

    $product = Product::factory()->create(['price' => 20.00, 'is_active' => true]);

    $data = new CreateOrderData(
        customerName: 'Jane',
        customerEmail: 'jane@example.com',
        deliveryDate: now()->addDay()->format('Y-m-d'),
        deliveryType: DeliveryType::Delivery->value,
        items: [['product_id' => $product->id, 'quantity' => 1]],
        deliveryTier: 'under5',
    );

    $payload = new OrderPipelineData($data);
    $pipe = new CalculateOrderTotals;

    $result = $pipe->handle($payload, fn ($p) => $p);

    expect($result->deliveryFee)->toBe(5.0)
        ->and($result->total)->toBe(25.0);
});

test('does not add delivery fee for pickup orders', function () {
    $product = Product::factory()->create(['price' => 20.00, 'is_active' => true]);

    $data = new CreateOrderData(
        customerName: 'Jane',
        customerEmail: 'jane@example.com',
        deliveryDate: now()->addDay()->format('Y-m-d'),
        deliveryType: DeliveryType::Pickup->value,
        items: [['product_id' => $product->id, 'quantity' => 1]],
    );

    $payload = new OrderPipelineData($data);
    $pipe = new CalculateOrderTotals;

    $result = $pipe->handle($payload, fn ($p) => $p);

    expect($result->deliveryFee)->toBe(0.0)
        ->and($result->total)->toBe(20.0);
});

test('adds tip to total when tipAmount is provided', function () {
    $product = Product::factory()->create(['price' => 20.00, 'is_active' => true]);

    $data = new CreateOrderData(
        customerName: 'Jane',
        customerEmail: 'jane@example.com',
        deliveryDate: now()->addDay()->format('Y-m-d'),
        deliveryType: DeliveryType::Pickup->value,
        items: [['product_id' => $product->id, 'quantity' => 1]],
        tipAmount: 4.0,
    );

    $payload = new OrderPipelineData($data);
    $pipe = new CalculateOrderTotals;

    $result = $pipe->handle($payload, fn ($p) => $p);

    expect($result->tipAmount)->toBe(4.0)
        ->and($result->total)->toBe(24.0);
});

test('clamps negative tipAmount to zero', function () {
    $product = Product::factory()->create(['price' => 20.00, 'is_active' => true]);

    $data = new CreateOrderData(
        customerName: 'Jane',
        customerEmail: 'jane@example.com',
        deliveryDate: now()->addDay()->format('Y-m-d'),
        deliveryType: DeliveryType::Pickup->value,
        items: [['product_id' => $product->id, 'quantity' => 1]],
        tipAmount: -5.0,
    );

    $payload = new OrderPipelineData($data);
    $pipe = new CalculateOrderTotals;

    $result = $pipe->handle($payload, fn ($p) => $p);

    expect($result->tipAmount)->toBe(0.0)
        ->and($result->total)->toBe(20.0);
});

test('tip stacks with delivery fee in total', function () {
    config(['kneadit.delivery_fees' => ['under5' => 5.00]]);

    $product = Product::factory()->create(['price' => 20.00, 'is_active' => true]);

    $data = new CreateOrderData(
        customerName: 'Jane',
        customerEmail: 'jane@example.com',
        deliveryDate: now()->addDay()->format('Y-m-d'),
        deliveryType: DeliveryType::Delivery->value,
        items: [['product_id' => $product->id, 'quantity' => 1]],
        deliveryTier: 'under5',
        tipAmount: 3.0,
    );

    $payload = new OrderPipelineData($data);
    $pipe = new CalculateOrderTotals;

    $result = $pipe->handle($payload, fn ($p) => $p);

    expect($result->subtotal)->toBe(20.0)
        ->and($result->deliveryFee)->toBe(5.0)
        ->and($result->tipAmount)->toBe(3.0)
        ->and($result->total)->toBe(28.0);
});
