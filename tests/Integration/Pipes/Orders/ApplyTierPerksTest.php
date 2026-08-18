<?php

use App\DataTransferObjects\Orders\CreateOrderData;
use App\Enums\Orders\DeliveryType;
use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
use App\Pipes\Orders\ApplyTierPerks;
use App\Pipes\Orders\OrderPipelineData;

beforeEach(function () {
    setUpTenantTest();
    settings([
        'loyalty_tier_gold_threshold' => 2000,
        'loyalty_tier_perks_enabled' => true,
        'loyalty_tier_gold_free_delivery' => true,
    ]);
});

function makePerksPayload(?Customer $customer, float $deliveryFee = 5.0): OrderPipelineData
{
    $payload = new OrderPipelineData(new CreateOrderData(
        customerName: 'Test',
        customerEmail: 'x@example.com',
        deliveryDate: now()->addDay()->format('Y-m-d'),
        deliveryType: DeliveryType::Delivery->value,
        items: [['product_id' => 1, 'quantity' => 1]],
    ));
    $payload->subtotal = 30.0;
    $payload->deliveryFee = $deliveryFee;
    $payload->total = $payload->subtotal + $deliveryFee;
    $payload->customer = $customer;

    return $payload;
}

test('zeros out delivery fee for a Gold customer', function () {
    $customer = Customer::factory()->create();
    LoyaltyPoint::factory()->earned(2000)->for($customer)->create();

    $result = resolve(ApplyTierPerks::class)->handle(makePerksPayload($customer), fn ($p) => $p);

    expect($result->deliveryFee)->toBe(0.0)
        ->and($result->total)->toBe(30.0);
});

test('leaves the fee untouched for a Bronze customer', function () {
    $customer = Customer::factory()->create();

    $result = resolve(ApplyTierPerks::class)->handle(makePerksPayload($customer), fn ($p) => $p);

    expect($result->deliveryFee)->toBe(5.0)
        ->and($result->total)->toBe(35.0);
});

test('skips when payload has no customer (defensive)', function () {
    $result = resolve(ApplyTierPerks::class)->handle(makePerksPayload(null), fn ($p) => $p);

    expect($result->deliveryFee)->toBe(5.0);
});

test('respects the global tierPerksEnabled toggle', function () {
    settings(['loyalty_tier_perks_enabled' => false]);
    $customer = Customer::factory()->create();
    LoyaltyPoint::factory()->earned(2000)->for($customer)->create();

    $result = resolve(ApplyTierPerks::class)->handle(makePerksPayload($customer), fn ($p) => $p);

    expect($result->deliveryFee)->toBe(5.0);
});
