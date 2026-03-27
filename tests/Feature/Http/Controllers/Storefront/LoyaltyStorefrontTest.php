<?php

use App\Models\Customer;
use App\Models\LoyaltyPoint;
use App\Models\LoyaltyReward;

beforeEach(function () {
    setUpTenantTest();
});

test('loyalty point model exists', function () {
    expect(class_exists(LoyaltyPoint::class))->toBeTrue();
});

test('loyalty reward model exists', function () {
    expect(class_exists(LoyaltyReward::class))->toBeTrue();
});

test('customer total points calculation', function () {
    $customer = Customer::query()->create([
        'name' => 'Test Customer',
        'email' => 'test@example.com',
    ]);

    LoyaltyPoint::query()->create([
        'customer_id' => $customer->id,
        'points' => 100,
        'type' => 'earned',
        'description' => 'Order #1',
    ]);

    LoyaltyPoint::query()->create([
        'customer_id' => $customer->id,
        'points' => 50,
        'type' => 'earned',
        'description' => 'Order #2',
    ]);

    LoyaltyPoint::query()->create([
        'customer_id' => $customer->id,
        'points' => 30,
        'type' => 'redeemed',
        'description' => 'Reward redeemed',
    ]);

    expect($customer->total_points)->toBe(120);
});

test('loyalty reward can be created', function () {
    $reward = LoyaltyReward::query()->create([
        'name' => 'Free Cookie',
        'description' => 'Get a free cookie!',
        'points_required' => 100,
        'reward_type' => 'free_product',
        'reward_value' => 0,
        'is_active' => true,
    ]);

    $this->assertDatabaseHas('loyalty_rewards', ['name' => 'Free Cookie']);
    expect($reward->is_active)->toBeTrue();
});

test('loyalty reward type label percentage', function () {
    $reward = LoyaltyReward::query()->create([
        'name' => '10% Off',
        'points_required' => 50,
        'reward_type' => 'percentage_discount',
        'reward_value' => 10,
        'is_active' => true,
    ]);

    expect($reward->reward_type_label)->toContain('% Off');
});

test('loyalty reward type label fixed', function () {
    $reward = LoyaltyReward::query()->create([
        'name' => '$5 Off',
        'points_required' => 75,
        'reward_type' => 'fixed_discount',
        'reward_value' => 5.00,
        'is_active' => true,
    ]);

    expect($reward->reward_type_label)->toBe('$5.00 Off');
});

test('customer lifetime points earned', function () {
    $customer = Customer::query()->create([
        'name' => 'Test Customer',
        'email' => 'lifetime@example.com',
    ]);

    LoyaltyPoint::query()->create([
        'customer_id' => $customer->id,
        'points' => 200,
        'type' => 'earned',
        'description' => 'Big order',
    ]);

    LoyaltyPoint::query()->create([
        'customer_id' => $customer->id,
        'points' => 50,
        'type' => 'redeemed',
        'description' => 'Reward',
    ]);

    expect($customer->lifetime_points_earned)->toBe(200);
});

test('loyalty points belong to customer', function () {
    $customer = Customer::query()->create([
        'name' => 'Test Customer',
        'email' => 'belong@example.com',
    ]);

    $point = LoyaltyPoint::query()->create([
        'customer_id' => $customer->id,
        'points' => 100,
        'type' => 'earned',
        'description' => 'Order',
    ]);

    expect($point->customer->id)->toBe($customer->id);
});
