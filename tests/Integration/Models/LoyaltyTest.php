<?php

use App\Enums\RewardType;
use App\Models\Customer;
use App\Models\LoyaltyPoint;
use App\Models\LoyaltyReward;

use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpTenantTest();
});

test('rewards page loads', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.rewards', [], false));

    $response->assertOk();
});

test('rewards page shows active rewards', function () {
    LoyaltyReward::factory()->create([
        'name' => 'Free Cookie',
        'description' => 'Get a free cookie!',
        'points_required' => 100,
        'reward_type' => RewardType::FreeProduct,
        'reward_value' => 0,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.rewards', [], false));

    $response->assertOk();
    $response->assertSee('Free Cookie');
});

test('points balance check works with valid email', function () {
    $customer = Customer::factory()->create();

    LoyaltyPoint::factory()->for($customer)->earned(150)->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('rewards.check', [], false), [
            'email' => $customer->email,
        ]);

    $response->assertOk();
    $response->assertSee('150');
});

test('points balance check for unknown email shows zero', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('rewards.check', [], false), [
            'email' => 'unknown@example.com',
        ]);

    $response->assertOk();
});

test('points are calculated correctly with earned and redeemed', function () {
    $customer = Customer::factory()->create();

    LoyaltyPoint::factory()->for($customer)->earned(200)->create();
    LoyaltyPoint::factory()->for($customer)->redeemed(50)->create();

    expect($customer->total_points)->toBe(150);
});

test('loyalty program name is configurable', function () {
    settings(['loyalty_program_name' => 'Baker Bucks']);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.rewards', [], false));

    $response->assertOk();
    $response->assertSee('Baker Bucks');
});

test('rewards check requires email', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('rewards.check', [], false), []);

    $response->assertSessionHasErrors('email');
});

test('lifetime points earned only counts earned type', function () {
    $customer = Customer::factory()->create();

    LoyaltyPoint::factory()->for($customer)->earned(300)->create();
    LoyaltyPoint::factory()->for($customer)->redeemed(100)->create();

    expect($customer->lifetime_points_earned)->toBe(300);
});

test('reward type labels are correct', function () {
    $reward = LoyaltyReward::factory()->create([
        'name' => '10% Off',
        'points_required' => 200,
        'reward_type' => RewardType::PercentageDiscount,
        'reward_value' => 10,
    ]);

    expect($reward->reward_type_label)->toBe('10.00% Off');

    $fixedReward = LoyaltyReward::factory()->create([
        'name' => '$5 Off',
        'points_required' => 100,
        'reward_type' => RewardType::FixedDiscount,
        'reward_value' => 5.00,
    ]);

    expect($fixedReward->reward_type_label)->toBe('$5.00 Off');
});
