<?php

use App\Enums\Engagement\RewardType;
use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
use App\Models\Engagement\LoyaltyReward;

beforeEach(function () {
    setUpTenantTest();
});

test('loyalty controller show passes vm to view', function () {
    $response = test()
        ->withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.rewards', [], false));

    $response->assertOk()
        ->assertViewHas('vm');
});

test('loyalty point model exists', function () {
    expect(class_exists(LoyaltyPoint::class))->toBeTrue();
});

test('loyalty reward model exists', function () {
    expect(class_exists(LoyaltyReward::class))->toBeTrue();
});

test('customer total points calculation', function () {
    $customer = Customer::factory()->create();

    LoyaltyPoint::factory()->for($customer)->earned(100)->create(['description' => 'Order #1']);
    LoyaltyPoint::factory()->for($customer)->earned(50)->create(['description' => 'Order #2']);
    LoyaltyPoint::factory()->for($customer)->redeemed(30)->create(['description' => 'Reward redeemed']);

    expect($customer->total_points)->toBe(120);
});

test('loyalty reward can be created', function () {
    $reward = LoyaltyReward::factory()->create([
        'name' => 'Free Cookie',
        'description' => 'Get a free cookie!',
        'points_required' => 100,
        'reward_type' => RewardType::FreeProduct,
        'reward_value' => 0,
    ]);

    test()->assertDatabaseHas('loyalty_rewards', ['name' => 'Free Cookie']);
    expect($reward->is_active)->toBeTrue();
});

test('loyalty reward type label percentage', function () {
    $reward = LoyaltyReward::factory()->create([
        'name' => '10% Off',
        'points_required' => 50,
        'reward_type' => RewardType::PercentageDiscount,
        'reward_value' => 10,
    ]);

    expect($reward->reward_type_label)->toContain('% Off');
});

test('loyalty reward type label fixed', function () {
    $reward = LoyaltyReward::factory()->create([
        'name' => '$5 Off',
        'points_required' => 75,
        'reward_type' => RewardType::FixedDiscount,
        'reward_value' => 5.00,
    ]);

    expect($reward->reward_type_label)->toBe('$5.00 Off');
});

test('customer lifetime points earned', function () {
    $customer = Customer::factory()->create();

    LoyaltyPoint::factory()->for($customer)->earned(200)->create(['description' => 'Big order']);
    LoyaltyPoint::factory()->for($customer)->redeemed(50)->create(['description' => 'Reward']);

    expect($customer->lifetime_points_earned)->toBe(200);
});

test('loyalty points belong to customer', function () {
    $customer = Customer::factory()->create();

    $point = LoyaltyPoint::factory()->for($customer)->earned(100)->create(['description' => 'Order']);

    expect($point->customer->id)->toBe($customer->id);
});
