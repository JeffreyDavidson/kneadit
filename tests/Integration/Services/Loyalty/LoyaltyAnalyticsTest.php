<?php

use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
use App\Models\Engagement\LoyaltyReward;
use App\Services\Loyalty\LoyaltyAnalytics;
use App\ValueObjects\LoyaltyMetrics;

beforeEach(function () {
    setUpTenantTest();
});

test('metrics returns a LoyaltyMetrics value object', function () {
    $customer = Customer::factory()->create();
    LoyaltyPoint::factory()->earned(100)->for($customer)->create();
    LoyaltyPoint::factory()->earned(50)->for($customer)->create();
    LoyaltyPoint::factory()->redeemed(30)->for($customer)->create();
    LoyaltyReward::factory()->count(2)->create();
    LoyaltyReward::factory()->inactive()->create();

    $metrics = resolve(LoyaltyAnalytics::class)->metrics();

    expect($metrics)
        ->toBeInstanceOf(LoyaltyMetrics::class)
        ->totalIssued->toBe(150)
        ->totalRedeemed->toBe(30)
        ->activeMembers->toBe(1)
        ->availableRewards->toBe(2);
});

test('metrics are cached within a request', function () {
    $analytics = resolve(LoyaltyAnalytics::class);

    $first = $analytics->metrics();
    $second = $analytics->metrics();

    expect($first)->toBe($second);
});

test('top customers returns customers ordered by balance', function () {
    $customer1 = Customer::factory()->create();
    $customer2 = Customer::factory()->create();

    LoyaltyPoint::factory()->earned(200)->for($customer1)->create();
    LoyaltyPoint::factory()->earned(500)->for($customer2)->create();

    $top = resolve(LoyaltyAnalytics::class)->topCustomers(2);

    expect($top)->toHaveCount(2)
        ->and($top->first()->id)->toBe($customer2->id);
});

test('leaderboard returns formatted array', function () {
    $customer = Customer::factory()->create();
    LoyaltyPoint::factory()->earned(100)->for($customer)->create();

    $leaderboard = resolve(LoyaltyAnalytics::class)->leaderboard();

    expect($leaderboard)->toHaveCount(1)
        ->and($leaderboard[0])->toHaveKeys(['name', 'points'])->name->toBe($customer->name)->points->toBe(100);
});

test('recent activity returns points with customers', function () {
    $customer = Customer::factory()->create();
    LoyaltyPoint::factory()->earned(50)->for($customer)->create();

    $activity = resolve(LoyaltyAnalytics::class)->recentActivity();

    expect($activity)->toHaveCount(1)
        ->and($activity->first()->customer)->not->toBeNull();
});

test('outstanding points returns total sum', function () {
    $customer = Customer::factory()->create();
    LoyaltyPoint::factory()->earned(100)->for($customer)->create();
    LoyaltyPoint::factory()->redeemed(30)->for($customer)->create();

    $outstanding = resolve(LoyaltyAnalytics::class)->outstandingPoints();

    // earned (100) + redeemed (-30 stored as 30) = 130 raw sum
    expect($outstanding)->toBe(130);
});

test('recent awards returns formatted array', function () {
    $customer = Customer::factory()->create();
    LoyaltyPoint::factory()->earned(75)->for($customer)->create();

    $awards = resolve(LoyaltyAnalytics::class)->recentAwards();

    expect($awards)->toHaveCount(1)
        ->and($awards[0])->toHaveKeys(['customer', 'points', 'description', 'date'])->customer->toBe($customer->name)->points->toBe(75);
});
