<?php

use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
use App\Services\Loyalty\CustomerLoyalty;

beforeEach(function () {
    setUpTenantTest();
    settings([
        'loyalty_tier_silver_threshold' => 500,
        'loyalty_tier_gold_threshold' => 2000,
        'loyalty_tier_platinum_threshold' => 5000,
        'loyalty_tier_perks_enabled' => true,
        'loyalty_tier_silver_multiplier' => '1.0',
        'loyalty_tier_silver_free_delivery' => false,
        'loyalty_tier_gold_multiplier' => '1.5',
        'loyalty_tier_gold_free_delivery' => true,
        'loyalty_tier_platinum_multiplier' => '2.0',
        'loyalty_tier_platinum_free_delivery' => true,
    ]);
    test()->customer = Customer::factory()->create();
});

test('Bronze customers get the default 1.0 multiplier and no free delivery', function () {
    $service = resolve(CustomerLoyalty::class);

    expect($service->pointsMultiplier(testFixture('customer', Customer::class)))->toBe(1.0)
        ->and($service->qualifiesForFreeDelivery(testFixture('customer', Customer::class)))->toBeFalse();
});

test('Gold customers get the configured multiplier and free delivery', function () {
    LoyaltyPoint::factory()->earned(2000)->for(testFixture('customer', Customer::class))->create();
    $service = resolve(CustomerLoyalty::class);

    expect($service->pointsMultiplier(testFixture('customer', Customer::class)))->toBe(1.5)
        ->and($service->qualifiesForFreeDelivery(testFixture('customer', Customer::class)))->toBeTrue();
});

test('Platinum customers get the configured multiplier and free delivery', function () {
    LoyaltyPoint::factory()->earned(5000)->for(testFixture('customer', Customer::class))->create();
    $service = resolve(CustomerLoyalty::class);

    expect($service->pointsMultiplier(testFixture('customer', Customer::class)))->toBe(2.0)
        ->and($service->qualifiesForFreeDelivery(testFixture('customer', Customer::class)))->toBeTrue();
});

test('returns 1.0 / false when perks are disabled even at top tier', function () {
    settings(['loyalty_tier_perks_enabled' => false]);
    LoyaltyPoint::factory()->earned(5000)->for(testFixture('customer', Customer::class))->create();
    $service = resolve(CustomerLoyalty::class);

    expect($service->pointsMultiplier(testFixture('customer', Customer::class)))->toBe(1.0)
        ->and($service->qualifiesForFreeDelivery(testFixture('customer', Customer::class)))->toBeFalse();
});
