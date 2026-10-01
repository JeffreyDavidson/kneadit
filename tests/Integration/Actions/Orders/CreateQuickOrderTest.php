<?php

use App\Actions\Orders\CreateQuickOrder;
use App\DataTransferObjects\Orders\CreateQuickOrderData;
use App\Enums\Orders\DeliveryType;
use App\Enums\Orders\PaymentMethod;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    actingAs(User::factory()->owner()->create());
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

dataset('quick order delivery pricing', [
    'first tier' => ['0', 10.00, 5.00],
    'second tier' => ['1', 10.00, 12.50],
    'free at the minimum' => ['1', 50.00, 0.00],
    'free over the minimum' => ['0', 60.00, 0.00],
]);

test('prices delivery by the bakery delivery tiers', function (string $tier, float $unitPrice, float $expectedFee) {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings([
        'freeDeliveryMinimum' => '50',
        'deliveryFeeTiers' => [
            ['description' => 'Local', 'fee' => 5.00],
            ['description' => 'Far', 'fee' => 12.50],
        ],
    ])));
    $product = Product::factory()->create(['price' => $unitPrice]);

    $order = resolve(CreateQuickOrder::class)(CreateQuickOrderData::fromArray([
        'customer_name' => 'Jane Doe',
        'customer_email' => 'jane@example.com',
        'delivery_date' => now()->addDays(3)->toDateString(),
        'delivery_time' => '14:00',
        'delivery_type' => DeliveryType::Delivery->value,
        'delivery_tier' => $tier,
        'delivery_address' => '1 Main St',
        'payment_method' => PaymentMethod::Cash->value,
        'order_items' => [
            ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => $unitPrice],
        ],
    ]));

    expect($order->delivery_fee->dollars())->toEqual($expectedFee)
        ->and($order->total->dollars())->toEqual($unitPrice + $expectedFee);
})->with('quick order delivery pricing');

test('does not charge a delivery fee for pickup', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings([
        'deliveryFeeTiers' => [['description' => 'Local', 'fee' => 5.00]],
    ])));
    $product = Product::factory()->create(['price' => 10.00]);

    $order = resolve(CreateQuickOrder::class)(CreateQuickOrderData::fromArray([
        'customer_name' => 'Jane Doe',
        'customer_email' => 'jane@example.com',
        'delivery_date' => now()->addDays(3)->toDateString(),
        'delivery_time' => '14:00',
        'delivery_type' => DeliveryType::Pickup->value,
        'delivery_tier' => '0',
        'payment_method' => PaymentMethod::Cash->value,
        'order_items' => [
            ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 10.00],
        ],
    ]));

    expect($order->delivery_fee->dollars())->toEqual(0.0)
        ->and($order->total->dollars())->toEqual(10.0);
});

test('charges no delivery fee when the bakery has no tiers configured', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings()));
    $product = Product::factory()->create(['price' => 10.00]);

    $order = resolve(CreateQuickOrder::class)(CreateQuickOrderData::fromArray([
        'customer_name' => 'Jane Doe',
        'customer_email' => 'jane@example.com',
        'delivery_date' => now()->addDays(3)->toDateString(),
        'delivery_time' => '14:00',
        'delivery_type' => DeliveryType::Delivery->value,
        'delivery_address' => '1 Main St',
        'payment_method' => PaymentMethod::Cash->value,
        'order_items' => [
            ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 10.00],
        ],
    ]));

    expect($order->delivery_fee->dollars())->toEqual(0.0);
});
