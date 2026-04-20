<?php

use App\Actions\Orders\CreateQuickOrder;
use App\DataTransferObjects\Orders\CreateQuickOrderData;
use App\Enums\Orders\DeliveryType;
use App\Enums\Orders\PaymentMethod;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
});

test('creates order with customer and items', function () {
    $product = Product::factory()->create(['price' => 10.00]);

    $data = CreateQuickOrderData::fromArray([
        'customer_name' => 'Jane Doe',
        'customer_email' => 'jane@example.com',
        'delivery_date' => now()->addDays(3)->toDateString(),
        'delivery_time' => '14:00',
        'delivery_type' => DeliveryType::Pickup->value,
        'payment_method' => PaymentMethod::Cash->value,
        'order_items' => [
            [
                'product_id' => $product->id,
                'quantity' => 2,
                'unit_price' => 10.00,
            ],
        ],
    ]);

    $order = resolve(CreateQuickOrder::class)($data);

    expect($order)
        ->toBeInstanceOf(Order::class)
        ->and($order->total->dollars())->toEqual(20.0)->and($order->orderItems)->toHaveCount(1)->and($order->customer->email)->toBe('jane@example.com');
});
