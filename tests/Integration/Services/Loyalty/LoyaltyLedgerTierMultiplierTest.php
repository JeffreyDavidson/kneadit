<?php

use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
use App\Models\Orders\Order;
use App\Services\Loyalty\LoyaltyLedger;

beforeEach(function () {
    setUpTenantTest();
    settings([
        'loyalty_enabled' => '1',
        'loyalty_points_per_dollar' => 10,
        'loyalty_tier_gold_threshold' => 2000,
        'loyalty_tier_perks_enabled' => true,
        'loyalty_tier_gold_multiplier' => '2.0',
    ]);
});

test('credits base points for a Bronze customer (no multiplier)', function () {
    $customer = Customer::factory()->create();
    $order = Order::factory()->for($customer)->create(['total' => 50.0]);

    $credit = resolve(LoyaltyLedger::class)->creditOrder($order);

    expect($credit?->points)->toBe(500);
});

test('multiplies points for a Gold customer when perks enabled', function () {
    $customer = Customer::factory()->create();
    LoyaltyPoint::factory()->earned(2000)->for($customer)->create();

    $order = Order::factory()->for($customer)->create(['total' => 50.0]);

    $credit = resolve(LoyaltyLedger::class)->creditOrder($order);

    expect($credit?->points)->toBe(1000);
});

test('does not multiply when perks are disabled even at Gold', function () {
    settings(['loyalty_tier_perks_enabled' => false]);
    $customer = Customer::factory()->create();
    LoyaltyPoint::factory()->earned(2000)->for($customer)->create();

    $order = Order::factory()->for($customer)->create(['total' => 50.0]);

    $credit = resolve(LoyaltyLedger::class)->creditOrder($order);

    expect($credit?->points)->toBe(500);
});
