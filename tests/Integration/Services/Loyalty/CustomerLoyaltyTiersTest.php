<?php

use App\Enums\Engagement\LoyaltyTier;
use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
use App\Services\Loyalty\CustomerLoyalty;

beforeEach(function () {
    setUpTenantTest();
    settings([
        'loyalty_tier_silver_threshold' => 500,
        'loyalty_tier_gold_threshold' => 2000,
        'loyalty_tier_platinum_threshold' => 5000,
    ]);
    test()->customer = Customer::factory()->create();
});

test('starts at Bronze with no earned points', function () {
    expect(resolve(CustomerLoyalty::class)->tier(test()->customer))->toBe(LoyaltyTier::Bronze);
});

test('promotes to Silver at the silver threshold', function () {
    LoyaltyPoint::factory()->earned(500)->for(test()->customer)->create();

    expect(resolve(CustomerLoyalty::class)->tier(test()->customer))->toBe(LoyaltyTier::Silver);
});

test('stays Bronze just below the silver threshold', function () {
    LoyaltyPoint::factory()->earned(499)->for(test()->customer)->create();

    expect(resolve(CustomerLoyalty::class)->tier(test()->customer))->toBe(LoyaltyTier::Bronze);
});

test('promotes to Gold at the gold threshold', function () {
    LoyaltyPoint::factory()->earned(2000)->for(test()->customer)->create();

    expect(resolve(CustomerLoyalty::class)->tier(test()->customer))->toBe(LoyaltyTier::Gold);
});

test('promotes to Platinum at the platinum threshold', function () {
    LoyaltyPoint::factory()->earned(5000)->for(test()->customer)->create();

    expect(resolve(CustomerLoyalty::class)->tier(test()->customer))->toBe(LoyaltyTier::Platinum);
});

test('tier is based on lifetime earned, not redeemable balance', function () {
    LoyaltyPoint::factory()->earned(2000)->for(test()->customer)->create();
    LoyaltyPoint::factory()->redeemed(1900)->for(test()->customer)->create();

    expect(resolve(CustomerLoyalty::class)->tier(test()->customer))->toBe(LoyaltyTier::Gold);
});

test('nextTierProgress reports the next tier and points needed', function () {
    LoyaltyPoint::factory()->earned(700)->for(test()->customer)->create();

    $progress = resolve(CustomerLoyalty::class)->nextTierProgress(test()->customer);

    expect($progress['next'])->toBe(LoyaltyTier::Gold);
    expect($progress['pointsToNext'])->toBe(1300);
});

test('nextTierProgress returns null next when at the top tier', function () {
    LoyaltyPoint::factory()->earned(6000)->for(test()->customer)->create();

    $progress = resolve(CustomerLoyalty::class)->nextTierProgress(test()->customer);

    expect($progress['next'])->toBeNull();
    expect($progress['pointsToNext'])->toBe(0);
});
