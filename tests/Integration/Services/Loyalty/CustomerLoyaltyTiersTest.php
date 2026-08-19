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
    expect(resolve(CustomerLoyalty::class)->tier(testFixture('customer', Customer::class)))->toBe(LoyaltyTier::Bronze);
});

test('promotes to Silver at the silver threshold', function () {
    LoyaltyPoint::factory()->earned(500)->for(testFixture('customer', Customer::class))->create();

    expect(resolve(CustomerLoyalty::class)->tier(testFixture('customer', Customer::class)))->toBe(LoyaltyTier::Silver);
});

test('stays Bronze just below the silver threshold', function () {
    LoyaltyPoint::factory()->earned(499)->for(testFixture('customer', Customer::class))->create();

    expect(resolve(CustomerLoyalty::class)->tier(testFixture('customer', Customer::class)))->toBe(LoyaltyTier::Bronze);
});

test('promotes to Gold at the gold threshold', function () {
    LoyaltyPoint::factory()->earned(2000)->for(testFixture('customer', Customer::class))->create();

    expect(resolve(CustomerLoyalty::class)->tier(testFixture('customer', Customer::class)))->toBe(LoyaltyTier::Gold);
});

test('promotes to Platinum at the platinum threshold', function () {
    LoyaltyPoint::factory()->earned(5000)->for(testFixture('customer', Customer::class))->create();

    expect(resolve(CustomerLoyalty::class)->tier(testFixture('customer', Customer::class)))->toBe(LoyaltyTier::Platinum);
});

test('tier is based on lifetime earned, not redeemable balance', function () {
    LoyaltyPoint::factory()->earned(2000)->for(testFixture('customer', Customer::class))->create();
    LoyaltyPoint::factory()->redeemed(1900)->for(testFixture('customer', Customer::class))->create();

    expect(resolve(CustomerLoyalty::class)->tier(testFixture('customer', Customer::class)))->toBe(LoyaltyTier::Gold);
});

test('nextTierProgress reports the next tier and points needed', function () {
    LoyaltyPoint::factory()->earned(700)->for(testFixture('customer', Customer::class))->create();

    $progress = resolve(CustomerLoyalty::class)->nextTierProgress(testFixture('customer', Customer::class));
    expect($progress)->toMatchArray(['next' => LoyaltyTier::Gold, 'pointsToNext' => 1300]);
});

test('nextTierProgress returns null next when at the top tier', function () {
    LoyaltyPoint::factory()->earned(6000)->for(testFixture('customer', Customer::class))->create();

    $progress = resolve(CustomerLoyalty::class)->nextTierProgress(testFixture('customer', Customer::class));

    expect($progress['next'])->toBeNull()
        ->and($progress['pointsToNext'])->toBe(0);
});
