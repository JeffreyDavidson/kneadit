<?php

use App\Models\Customer;
use App\Models\LoyaltyPoint;
use App\Models\LoyaltyReward;
use App\Models\Setting;

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
    LoyaltyReward::query()->create([
        'name' => 'Free Cookie',
        'description' => 'Get a free cookie!',
        'points_required' => 100,
        'reward_type' => 'free_product',
        'reward_value' => 0,
        'is_active' => true,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.rewards', [], false));

    $response->assertOk();
    $response->assertSee('Free Cookie');
});

test('points balance check works with valid email', function () {
    $customer = Customer::query()->create([
        'name' => 'Loyal Customer',
        'email' => 'loyal@example.com',
    ]);

    LoyaltyPoint::query()->create([
        'customer_id' => $customer->id,
        'points' => 150,
        'type' => 'earned',
        'description' => 'Order reward',
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('rewards.check', [], false), [
            'email' => 'loyal@example.com',
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
    $customer = Customer::query()->create([
        'name' => 'Active Customer',
        'email' => 'active@example.com',
    ]);

    LoyaltyPoint::query()->create([
        'customer_id' => $customer->id,
        'points' => 200,
        'type' => 'earned',
        'description' => 'Order 1',
    ]);

    LoyaltyPoint::query()->create([
        'customer_id' => $customer->id,
        'points' => 50,
        'type' => 'redeemed',
        'description' => 'Redeemed reward',
    ]);

    expect($customer->total_points)->toBe(150);
});

test('loyalty program name is configurable', function () {
    Setting::set('loyalty_program_name', 'Baker Bucks');

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
    $customer = Customer::query()->create([
        'name' => 'Test',
        'email' => 'test@example.com',
    ]);

    LoyaltyPoint::query()->create([
        'customer_id' => $customer->id,
        'points' => 300,
        'type' => 'earned',
        'description' => 'Earned',
    ]);

    LoyaltyPoint::query()->create([
        'customer_id' => $customer->id,
        'points' => 100,
        'type' => 'redeemed',
        'description' => 'Redeemed',
    ]);

    expect($customer->lifetime_points_earned)->toBe(300);
});

test('reward type labels are correct', function () {
    $reward = LoyaltyReward::query()->create([
        'name' => '10% Off',
        'points_required' => 200,
        'reward_type' => 'percentage_discount',
        'reward_value' => 10,
        'is_active' => true,
    ]);

    expect($reward->reward_type_label)->toBe('10.00% Off');

    $fixedReward = LoyaltyReward::query()->create([
        'name' => '$5 Off',
        'points_required' => 100,
        'reward_type' => 'fixed_discount',
        'reward_value' => 5.00,
        'is_active' => true,
    ]);

    expect($fixedReward->reward_type_label)->toBe('$5.00 Off');
});
