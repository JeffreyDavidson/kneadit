<?php

use App\DataTransferObjects\Orders\CreateOrderData;
use App\Enums\Orders\DeliveryType;
use App\Exceptions\Orders\MinimumOrderAmountNotMetException;
use App\Pipes\Orders\EnforceMinimumOrderAmount;
use App\Pipes\Orders\OrderPipelineData;
use App\Services\Settings\TenantSettings;

function makeSettingsForMinimumPipeTest(string $pickupMin, string $deliveryMin): TenantSettings
{
    return makeTenantSettings(orders: makeOrderSettings([
        'minimumPickupOrderAmount' => $pickupMin,
        'minimumDeliveryOrderAmount' => $deliveryMin,
    ]));
}

function runMinimumPipe(float $subtotal, string $deliveryType, string $pickupMin, string $deliveryMin): OrderPipelineData
{
    $data = new CreateOrderData(
        customerName: 'Jane',
        customerEmail: 'jane@example.com',
        deliveryDate: now()->addDay()->format('Y-m-d'),
        deliveryType: $deliveryType,
        items: [],
    );

    $payload = new OrderPipelineData($data);
    $payload->subtotal = $subtotal;

    $pipe = new EnforceMinimumOrderAmount(makeSettingsForMinimumPipeTest($pickupMin, $deliveryMin));

    $result = $pipe->handle($payload, fn ($p) => $p);

    if (! $result instanceof OrderPipelineData) {
        throw new RuntimeException('Expected the minimum-order pipe to return its payload.');
    }

    return $result;
}

test('passes through when minimum is zero', function () {
    $result = runMinimumPipe(5.0, DeliveryType::Pickup->value, '0', '0');

    expect($result->cancelled)->toBeFalse();
});

test('passes through when subtotal meets pickup minimum', function () {
    $result = runMinimumPipe(15.0, DeliveryType::Pickup->value, '15', '0');

    expect($result->cancelled)->toBeFalse();
});

test('throws when pickup subtotal is below minimum', function () {
    runMinimumPipe(10.0, DeliveryType::Pickup->value, '15', '0');
})->throws(MinimumOrderAmountNotMetException::class);

test('throws when delivery subtotal is below minimum', function () {
    runMinimumPipe(10.0, DeliveryType::Delivery->value, '0', '25');
})->throws(MinimumOrderAmountNotMetException::class);

test('uses pickup minimum for pickup orders even when delivery minimum is higher', function () {
    $result = runMinimumPipe(10.0, DeliveryType::Pickup->value, '0', '25');

    expect($result->cancelled)->toBeFalse();
});

test('exception carries context about the shortfall', function () {
    try {
        runMinimumPipe(8.0, DeliveryType::Delivery->value, '0', '20');
    } catch (MinimumOrderAmountNotMetException $e) {
        expect($e->deliveryType)->toBe(DeliveryType::Delivery->value)
            ->and($e->subtotal)->toBe(8.0)
            ->and($e->minimum)->toBe(20.0);

        return;
    }

    test()->fail('Expected exception was not thrown');
});
