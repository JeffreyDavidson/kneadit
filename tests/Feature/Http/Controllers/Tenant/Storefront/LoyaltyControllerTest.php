<?php

use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
use App\Models\Engagement\LoyaltyReward;
use App\Presenters\LoyaltyRewardPresenter;
use App\Services\Customers\CustomerIntelligence;

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

    expect(resolve(CustomerIntelligence::class)->metrics($customer)->totalPoints)->toBe(120);
});

test('loyalty reward can be created', function () {
    $reward = LoyaltyReward::factory()->freeProduct()->create([
        'name' => 'Free Cookie',
        'description' => 'Get a free cookie!',
        'points_required' => 100,
    ]);

    test()->assertDatabaseHas('loyalty_rewards', ['name' => 'Free Cookie']);
    expect($reward->is_active)->toBeTrue();
});

test('loyalty reward type label percentage', function () {
    $reward = LoyaltyReward::factory()->percentageDiscount()->create([
        'name' => '10% Off',
        'points_required' => 50,
        'discount_percentage' => 10,
    ]);

    expect(LoyaltyRewardPresenter::for($reward)->rewardTypeLabel())->toContain('% Off');
});

test('loyalty reward type label fixed', function () {
    $reward = LoyaltyReward::factory()->fixedDiscount()->create([
        'name' => '$5 Off',
        'points_required' => 75,
        'discount_amount' => 5.00,
    ]);

    expect(LoyaltyRewardPresenter::for($reward)->rewardTypeLabel())->toBe('$5.00 Off');
});

test('customer lifetime points earned', function () {
    $customer = Customer::factory()->create();

    LoyaltyPoint::factory()->for($customer)->earned(200)->create(['description' => 'Big order']);
    LoyaltyPoint::factory()->for($customer)->redeemed(50)->create(['description' => 'Reward']);

    expect(resolve(CustomerIntelligence::class)->metrics($customer)->lifetimePointsEarned)->toBe(200);
});

test('loyalty points belong to customer', function () {
    $customer = Customer::factory()->create();

    $point = LoyaltyPoint::factory()->for($customer)->earned(100)->create(['description' => 'Order']);

    expect($point->customer->id)->toBe($customer->id);
});

test('rewards history shows the signed points for each entry type', function (callable $seed, string $expected, string $unexpected) {
    $customer = Customer::factory()->create();
    $seed($customer);

    $response = test()
        ->withoutMiddleware(tenantMiddleware())
        ->post(route('rewards.check', [], false), ['email' => $customer->email]);

    $text = preg_replace('/\s+/', '', strip_tags($response->getContent()));
    $response->assertOk();
    expect($text)
        ->toContain($expected)
        ->not->toContain($unexpected);
})->with([
    'earned' => [fn (Customer $customer) => LoyaltyPoint::factory()->earned(75)->for($customer)->create(), '+75', '-75'],
    'redeemed' => [fn (Customer $customer) => LoyaltyPoint::factory()->redeemed(100)->for($customer)->create(), '-100', '+100'],
    'negative adjustment' => [fn (Customer $customer) => LoyaltyPoint::factory()->adjusted(-25)->for($customer)->create(), '-25', '+-25'],
    'positive adjustment' => [fn (Customer $customer) => LoyaltyPoint::factory()->adjusted(40)->for($customer)->create(), '+40', '+-40'],
]);
