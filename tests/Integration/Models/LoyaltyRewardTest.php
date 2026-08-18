<?php

use App\Models\Engagement\LoyaltyReward;
use App\Models\Inventory\Product;
use App\Models\Staff\User;
use App\Presenters\LoyaltyRewardPresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    User::factory()->owner()->create();
});

test('reward type label for percentage discount', function () {
    $reward = LoyaltyReward::factory()->percentageDiscount()->create([
        'name' => '10% Off',
        'points_required' => 100,
        'discount_percentage' => 10.00,
    ]);

    expect(LoyaltyRewardPresenter::for($reward)->rewardTypeLabel())->toBe('10.00% Off');
});

test('reward type label for fixed discount', function () {
    $reward = LoyaltyReward::factory()->fixedDiscount()->create([
        'name' => '$5 Off',
        'points_required' => 50,
        'discount_amount' => 5.00,
    ]);

    expect(LoyaltyRewardPresenter::for($reward)->rewardTypeLabel())->toBe('$5.00 Off');
});

test('reward type label for free product', function () {
    $product = Product::factory()->create(['name' => 'Sourdough']);

    $reward = LoyaltyReward::factory()->freeProduct()->for($product)->create([
        'name' => 'Free Sourdough',
        'points_required' => 200,
    ]);

    expect(LoyaltyRewardPresenter::for($reward)->rewardTypeLabel())->toBe('Free Sourdough');
});

test('reward belongs to product', function () {
    $product = Product::factory()->create(['name' => 'Sourdough']);

    $reward = LoyaltyReward::factory()->freeProduct()->for($product)->create([
        'name' => 'Free Sourdough',
        'points_required' => 200,
    ]);

    expect($reward->product)->toBeInstanceOf(Product::class);
});

test('is active is cast to boolean', function () {
    $reward = LoyaltyReward::factory()->percentageDiscount()->create([
        'discount_percentage' => 10,
    ]);

    expect($reward->is_active)->toBeBool();
});
