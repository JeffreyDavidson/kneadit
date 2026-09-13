<?php

use App\DataTransferObjects\Orders\CreateOrderData;
use App\Enums\Orders\DeliveryType;
use App\Pipes\Orders\ApplySitewideSale;
use App\Pipes\Orders\OrderPipelineData;
use App\ValueObjects\Money;

beforeEach(fn () => setUpTenantTest());

function makeSalePayload(float $subtotal = 100.0): OrderPipelineData
{
    $payload = new OrderPipelineData(new CreateOrderData(
        customerName: 'x',
        customerEmail: 'x@example.com',
        deliveryDate: now()->addDay()->format('Y-m-d'),
        deliveryType: DeliveryType::Pickup->value,
        items: [['product_id' => 1, 'quantity' => 1]],
    ));
    $payload->subtotal = Money::fromDollars($subtotal);
    $payload->recalculateTotal();

    return $payload;
}

test('skips when sale is disabled', function () {
    settings(['sitewide_sale_enabled' => false, 'sitewide_sale_percent' => 15]);

    $result = resolve(ApplySitewideSale::class)->handle(makeSalePayload(), fn ($p) => $p);

    expect($result->discountAmount->dollars())->toBe(0.0)
        ->and($result->total->dollars())->toBe(100.0);
});

test('skips when sale percent is 0', function () {
    settings(['sitewide_sale_enabled' => true, 'sitewide_sale_percent' => 0]);

    $result = resolve(ApplySitewideSale::class)->handle(makeSalePayload(), fn ($p) => $p);

    expect($result->discountAmount->dollars())->toBe(0.0);
});

test('applies the discount to total when enabled', function () {
    settings(['sitewide_sale_enabled' => true, 'sitewide_sale_percent' => 15]);

    $result = resolve(ApplySitewideSale::class)->handle(makeSalePayload(), fn ($p) => $p);

    expect($result->discountAmount->dollars())->toBe(15.0)
        ->and($result->total->dollars())->toBe(85.0);
});

test('clamps percent above 100 (defensive)', function () {
    settings(['sitewide_sale_enabled' => true, 'sitewide_sale_percent' => 150]);

    $result = resolve(ApplySitewideSale::class)->handle(makeSalePayload(), fn ($p) => $p);

    expect($result->discountAmount->dollars())->toBe(100.0)
        ->and($result->total->dollars())->toBe(0.0);
});

test('does nothing when subtotal is 0', function () {
    settings(['sitewide_sale_enabled' => true, 'sitewide_sale_percent' => 15]);

    $payload = makeSalePayload(0.0);
    $result = resolve(ApplySitewideSale::class)->handle($payload, fn ($p) => $p);

    expect($result->discountAmount->dollars())->toBe(0.0);
});
