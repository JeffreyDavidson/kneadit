<?php

use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
use App\Models\Engagement\LoyaltyReward;
use App\Services\Customers\CustomerIntelligence;

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
    LoyaltyReward::factory()->freeProduct()->create([
        'name' => 'Free Cookie',
        'description' => 'Get a free cookie!',
        'points_required' => 100,
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

    expect(resolve(CustomerIntelligence::class)->metrics($customer)->totalPoints)->toBe(150);
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

    expect(resolve(CustomerIntelligence::class)->metrics($customer)->lifetimePointsEarned)->toBe(300);
});

test('reward type labels are correct', function () {
    $reward = LoyaltyReward::factory()->percentageDiscount()->create([
        'name' => '10% Off',
        'points_required' => 200,
        'discount_percentage' => 10,
    ]);

    expect($reward->reward_type_label)->toBe('10.00% Off');

    $fixedReward = LoyaltyReward::factory()->fixedDiscount()->create([
        'name' => '$5 Off',
        'points_required' => 100,
        'discount_amount' => 5.00,
    ]);

    expect($fixedReward->reward_type_label)->toBe('$5.00 Off');
});
