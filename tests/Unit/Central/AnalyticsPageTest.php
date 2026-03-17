<?php

use App\Filament\Central\Pages\Analytics;
use Tests\CentralTestCase;

uses(CentralTestCase::class);

beforeEach(function () {
    test()->page = new Analytics;
});

test('get signups by month returns 12 months', function () {
    $result = test()->page->getSignupsByMonth();

    expect($result)->toHaveCount(12);
    expect($result[0])->toHaveKey('label');
    expect($result[0])->toHaveKey('count');
});

test('get plan distribution', function () {
    $this->createTenant(['id' => 'b1', 'name' => 'B1', 'email' => 'b1@test.com', 'plan' => 'starter']);
    $this->createTenant(['id' => 'b2', 'name' => 'B2', 'email' => 'b2@test.com', 'plan' => 'starter']);
    $this->createTenant(['id' => 'b3', 'name' => 'B3', 'email' => 'b3@test.com', 'plan' => 'growth']);

    $result = test()->page->getPlanDistribution();

    expect($result['starter'])->toBe(2);
    expect($result['growth'])->toBe(1);
});

test('get trial conversion', function () {
    $this->createTenant(['id' => 't1', 'name' => 'T1', 'email' => 't1@test.com', 'plan' => 'starter', 'trial_ends_at' => now()->addDays(7)]);
    $this->createTenant(['id' => 't2', 'name' => 'T2', 'email' => 't2@test.com', 'plan' => 'starter', 'trial_ends_at' => now()->subDays(7)]);
    $this->createTenant(['id' => 't3', 'name' => 'T3', 'email' => 't3@test.com', 'plan' => 'growth']);

    $result = test()->page->getTrialConversion();

    expect($result)->toHaveKey('on_trial');
    expect($result)->toHaveKey('expired');
    expect($result)->toHaveKey('converted');
    expect($result['on_trial'])->toBe(1);
    expect($result['expired'])->toBe(1);
    expect($result['converted'])->toBe(1);
});

test('get total signups', function () {
    $this->createTenant(['id' => 's1', 'name' => 'S1', 'email' => 's1@test.com', 'plan' => 'starter']);
    $this->createTenant(['id' => 's2', 'name' => 'S2', 'email' => 's2@test.com', 'plan' => 'growth']);

    expect(test()->page->getTotalSignups())->toBe(2);
});

test('get this month signups', function () {
    $this->createTenant(['id' => 'm1', 'name' => 'M1', 'email' => 'm1@test.com', 'plan' => 'starter']);
    $this->createTenant(['id' => 'm2', 'name' => 'M2', 'email' => 'm2@test.com', 'plan' => 'starter']);

    expect(test()->page->getThisMonthSignups())->toBe(2);
});

test('get avg days on trial', function () {
    $this->createTenant(['id' => 'a1', 'name' => 'A1', 'email' => 'a1@test.com', 'plan' => 'starter', 'trial_ends_at' => now()->addDays(14)]);
    $this->createTenant(['id' => 'a2', 'name' => 'A2', 'email' => 'a2@test.com', 'plan' => 'starter', 'trial_ends_at' => now()->addDays(14)]);

    $result = test()->page->getAvgDaysOnTrial();

    expect($result)->toBeFloat();
    expect($result)->toBe(14.0);
});

test('get avg days on trial returns zero when no trials', function () {
    expect(test()->page->getAvgDaysOnTrial())->toBe(0);
});

test('get most popular plan', function () {
    $this->createTenant(['id' => 'p1', 'name' => 'P1', 'email' => 'p1@test.com', 'plan' => 'starter']);
    $this->createTenant(['id' => 'p2', 'name' => 'P2', 'email' => 'p2@test.com', 'plan' => 'starter']);
    $this->createTenant(['id' => 'p3', 'name' => 'P3', 'email' => 'p3@test.com', 'plan' => 'growth']);

    expect(test()->page->getMostPopularPlan())->toBe('starter');
});

test('get most popular plan returns na when no tenants', function () {
    expect(test()->page->getMostPopularPlan())->toBe('N/A');
});
