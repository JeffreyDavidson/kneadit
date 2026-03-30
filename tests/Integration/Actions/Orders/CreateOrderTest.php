<?php

use App\Actions\Orders\CreateOrder;
use App\DataTransferObjects\CreateOrderData;
use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Mail\OrderPlacedMail;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Mail::fake();
});

test('creates order with correct totals and items', function () {
    $product = Product::factory()->create(['price' => 12.50]);

    $order = resolve(CreateOrder::class)(
        CreateOrderData::fromArray([
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'delivery_date' => now()->addDays(5)->toDateString(),
            'delivery_type' => DeliveryType::Pickup->value,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ])
    );

    expect($order)
        ->not->toBeNull()
        ->status->toBe(OrderStatus::Pending)
        ->subtotal->toBe('25.00')
        ->total->toBe('25.00');

    $order->load('orderItems', 'customer');
    expect($order->orderItems)->toHaveCount(1)->and($order->customer->email)->toBe('jane@example.com');
});

test('returns null when capacity is full', function () {
    $product = Product::factory()->create(['price' => 10.00]);
    $deliveryDate = now()->addDays(5)->toDateString();

    settings(['default_daily_capacity' => '1']);
    Order::factory()->create(['delivery_date' => $deliveryDate, 'status' => OrderStatus::Confirmed]);

    $order = resolve(CreateOrder::class)(
        CreateOrderData::fromArray([
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'delivery_date' => $deliveryDate,
            'delivery_type' => DeliveryType::Pickup->value,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ])
    );

    expect($order)->toBeNull();
});

test('sends order placed email to customer on creation', function () {
    $product = Product::factory()->create(['price' => 10.00]);

    resolve(CreateOrder::class)(
        CreateOrderData::fromArray([
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'delivery_date' => now()->addDays(5)->toDateString(),
            'delivery_type' => DeliveryType::Pickup->value,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ])
    );

    Mail::assertQueued(OrderPlacedMail::class);
});
