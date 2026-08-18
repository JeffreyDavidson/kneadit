<?php

use App\Actions\Orders\CreateOrder;
use App\DataTransferObjects\Orders\CreateOrderData;
use App\Enums\Orders\DeliveryType;
use App\Models\Inventory\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Mail::fake();
});

test('persists all three pickup contact fields', function () {
    $product = Product::factory()->create(['price' => 12.50]);

    $order = resolve(CreateOrder::class)(
        CreateOrderData::fromArray([
            'customer_name' => 'Alice',
            'customer_email' => 'alice@example.com',
            'delivery_date' => now()->addDays(5)->toDateString(),
            'delivery_type' => DeliveryType::Pickup->value,
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'pickup_contact_name' => 'Bob (husband)',
            'pickup_contact_phone' => '555-9876',
            'pickup_contact_email' => 'bob@example.com',
        ])
    );

    expect($order)->not->toBeNull()
        ->and($order->pickup_contact_name)->toBe('Bob (husband)')
        ->and($order->pickup_contact_phone)->toBe('555-9876')
        ->and($order->pickup_contact_email)->toBe('bob@example.com');
});

test('omitting pickup contact leaves columns null', function () {
    $product = Product::factory()->create(['price' => 12.50]);

    $order = resolve(CreateOrder::class)(
        CreateOrderData::fromArray([
            'customer_name' => 'Alice',
            'customer_email' => 'alice@example.com',
            'delivery_date' => now()->addDays(5)->toDateString(),
            'delivery_type' => DeliveryType::Pickup->value,
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ])
    );

    expect($order)->not->toBeNull()
        ->and($order->pickup_contact_name)->toBeNull()
        ->and($order->pickup_contact_phone)->toBeNull()
        ->and($order->pickup_contact_email)->toBeNull();
});
