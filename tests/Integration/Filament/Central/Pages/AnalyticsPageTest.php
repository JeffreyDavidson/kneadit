<?php

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Central\Pages\Analytics;

beforeEach(function () {
    setUpCentralTest();
    test()->page = new Analytics;
});

test('get signups by month returns 12 months', function () {
    $result = test()->page->getSignupsByMonth();

    expect($result)->toHaveCount(12)->and($result[0])->toHaveKeys(['label', 'count']);
});

test('get plan distribution', function () {
    createTenant(['id' => 'b1', 'name' => 'B1', 'email' => 'b1@test.com', 'plan' => SubscriptionTier::Starter]);
    createTenant(['id' => 'b2', 'name' => 'B2', 'email' => 'b2@test.com', 'plan' => SubscriptionTier::Starter]);
    createTenant(['id' => 'b3', 'name' => 'B3', 'email' => 'b3@test.com', 'plan' => SubscriptionTier::Growth]);

    $result = test()->page->getPlanDistribution();
    expect($result)->toMatchArray(['starter' => 2, 'growth' => 1]);
});

test('get trial conversion', function () {
    createTenant(['id' => 't1', 'name' => 'T1', 'email' => 't1@test.com', 'plan' => SubscriptionTier::Starter, 'trial_ends_at' => now()->addDays(7)]);
    createTenant(['id' => 't2', 'name' => 'T2', 'email' => 't2@test.com', 'plan' => SubscriptionTier::Starter, 'trial_ends_at' => now()->subDays(7)]);
    createTenant(['id' => 't3', 'name' => 'T3', 'email' => 't3@test.com', 'plan' => SubscriptionTier::Growth]);

    $result = test()->page->getTrialConversion();

    expect($result)->toHaveKeys(['on_trial', 'expired', 'converted'])->toMatchArray(['on_trial' => 1, 'expired' => 1, 'converted' => 1]);
});

test('get total signups', function () {
    createTenant(['id' => 's1', 'name' => 'S1', 'email' => 's1@test.com', 'plan' => SubscriptionTier::Starter]);
    createTenant(['id' => 's2', 'name' => 'S2', 'email' => 's2@test.com', 'plan' => SubscriptionTier::Growth]);

    expect(test()->page->getTotalSignups())->toBe(2);
});

test('get this month signups', function () {
    createTenant(['id' => 'm1', 'name' => 'M1', 'email' => 'm1@test.com', 'plan' => SubscriptionTier::Starter]);
    createTenant(['id' => 'm2', 'name' => 'M2', 'email' => 'm2@test.com', 'plan' => SubscriptionTier::Starter]);

    expect(test()->page->getThisMonthSignups())->toBe(2);
});

test('get avg days on trial', function () {
    createTenant(['id' => 'a1', 'name' => 'A1', 'email' => 'a1@test.com', 'plan' => SubscriptionTier::Starter, 'trial_ends_at' => now()->addDays(14)]);
    createTenant(['id' => 'a2', 'name' => 'A2', 'email' => 'a2@test.com', 'plan' => SubscriptionTier::Starter, 'trial_ends_at' => now()->addDays(14)]);

    $result = test()->page->getAvgDaysOnTrial();

    expect($result)->toBeFloat()->toBe(14.0);
});

test('get avg days on trial returns zero when no trials', function () {
    expect(test()->page->getAvgDaysOnTrial())->toBe(0.0);
});

test('get most popular plan', function () {
    createTenant(['id' => 'p1', 'name' => 'P1', 'email' => 'p1@test.com', 'plan' => SubscriptionTier::Starter]);
    createTenant(['id' => 'p2', 'name' => 'P2', 'email' => 'p2@test.com', 'plan' => SubscriptionTier::Starter]);
    createTenant(['id' => 'p3', 'name' => 'P3', 'email' => 'p3@test.com', 'plan' => SubscriptionTier::Growth]);

    expect(test()->page->getMostPopularPlan())->toBe('starter');
});

test('get most popular plan returns na when no tenants', function () {
    expect(test()->page->getMostPopularPlan())->toBe('N/A');
});
