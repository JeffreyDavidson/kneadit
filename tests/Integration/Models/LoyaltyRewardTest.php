<?php

use App\Enums\Engagement\RewardType;
use App\Models\Engagement\LoyaltyReward;
use App\Models\Inventory\Product;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    User::factory()->owner()->create();
});

test('reward type label for percentage discount', function () {
    $reward = LoyaltyReward::factory()->create([
        'name' => '10% Off',
        'points_required' => 100,
        'reward_type' => RewardType::PercentageDiscount,
        'reward_value' => 10.00,
    ]);

    expect($reward->reward_type_label)->toBe('10.00% Off');
});

test('reward type label for fixed discount', function () {
    $reward = LoyaltyReward::factory()->create([
        'name' => '$5 Off',
        'points_required' => 50,
        'reward_type' => RewardType::FixedDiscount,
        'reward_value' => 5.00,
    ]);

    expect($reward->reward_type_label)->toBe('$5.00 Off');
});

test('reward type label for free product', function () {
    $product = Product::factory()->create(['name' => 'Sourdough']);

    $reward = LoyaltyReward::factory()->for($product)->create([
        'name' => 'Free Sourdough',
        'points_required' => 200,
        'reward_type' => RewardType::FreeProduct,
        'reward_value' => 0,
    ]);

    expect($reward->reward_type_label)->toBe('Free Sourdough');
});

test('reward belongs to product', function () {
    $product = Product::factory()->create(['name' => 'Sourdough']);

    $reward = LoyaltyReward::factory()->for($product)->create([
        'name' => 'Free Sourdough',
        'points_required' => 200,
        'reward_type' => RewardType::FreeProduct,
        'reward_value' => 0,
    ]);

    expect($reward->product)->toBeInstanceOf(Product::class);
});

test('is active is cast to boolean', function () {
    $reward = LoyaltyReward::factory()->create([
        'reward_type' => RewardType::PercentageDiscount,
        'reward_value' => 10,
    ]);

    expect($reward->is_active)->toBeBool();
});
