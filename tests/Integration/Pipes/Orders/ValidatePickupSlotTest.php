<?php

use App\Actions\Orders\CreateOrder;
use App\DataTransferObjects\Orders\CreateOrderData;
use App\Enums\Orders\DeliveryType;
use App\Enums\Orders\OrderStatus;
use App\Exceptions\Orders\PickupSlotUnavailableException;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Pipes\Orders\OrderPipelineData;
use App\Pipes\Orders\ValidatePickupSlot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Mail::fake();
    settings([
        'pickup_slots_enabled' => true,
        'pickup_slot_interval_minutes' => 30,
        'pickup_slot_max_per_window' => 2,
    ]);
});

function pickupSlotPayload(string $deliveryType = 'pickup', ?string $time = '08:30'): OrderPipelineData
{
    return new OrderPipelineData(new CreateOrderData(
        customerName: 'Jane',
        customerEmail: 'jane@example.com',
        deliveryDate: '2026-05-04',
        deliveryType: $deliveryType,
        items: [],
        deliveryTime: $time,
    ));
}

function bookPickupSlot(int $count, string $time = '08:30', OrderStatus $status = OrderStatus::Confirmed, string $type = 'pickup'): void
{
    Order::factory()->count($count)->create([
        'status' => $status,
        'delivery_date' => '2026-05-04',
        'delivery_time' => $time,
        'delivery_type' => $type,
    ]);
}

function runPickupSlotPipe(OrderPipelineData $payload): mixed
{
    return resolve(ValidatePickupSlot::class)->handle($payload, fn (OrderPipelineData $passed): OrderPipelineData => $passed);
}

test('a slot with room left passes through', function () {
    bookPickupSlot(1);

    $result = runPickupSlotPipe(pickupSlotPayload());

    expect($result)->toBeInstanceOf(OrderPipelineData::class)
        ->and($result->cancelled)->toBeFalse();
});

test('a full slot throws', function () {
    bookPickupSlot(2);

    runPickupSlotPipe(pickupSlotPayload());
})->throws(PickupSlotUnavailableException::class);

test('only active pickup orders at that time count toward the slot', function () {
    bookPickupSlot(1, status: OrderStatus::Cancelled);
    bookPickupSlot(1, type: DeliveryType::Delivery->value);
    bookPickupSlot(1, time: '09:00');
    bookPickupSlot(1);

    $result = runPickupSlotPipe(pickupSlotPayload());

    expect($result)->toBeInstanceOf(OrderPipelineData::class);
});

test('the pipe does nothing when slots are disabled, for deliveries, or without a time', function (bool $enabled, string $type, ?string $time) {
    settings(['pickup_slots_enabled' => $enabled]);
    bookPickupSlot(2);

    $result = runPickupSlotPipe(pickupSlotPayload($type, $time));

    expect($result)->toBeInstanceOf(OrderPipelineData::class);
})->with([
    'slots disabled' => [false, 'pickup', '08:30'],
    'a delivery order' => [true, 'delivery', '08:30'],
    'no time' => [true, 'pickup', null],
]);

test('creating an order for a full slot throws and persists nothing', function () {
    bookPickupSlot(2);
    $product = Product::factory()->create();

    $create = fn () => resolve(CreateOrder::class)(CreateOrderData::fromArray([
        'customer_name' => 'Jane',
        'customer_email' => 'jane@example.com',
        'delivery_date' => '2026-05-04',
        'delivery_time' => '08:30',
        'delivery_type' => DeliveryType::Pickup->value,
        'items' => [['product_id' => $product->id, 'quantity' => 1]],
    ]));

    expect($create)->toThrow(PickupSlotUnavailableException::class)
        ->and(Order::query()->count())->toBe(2);
});
