<?php

use App\Filament\Central\Pages\BakeryInsights;

beforeEach(function () {
    setUpCentralTest();
    test()->page = new BakeryInsights;
});

test('active tab defaults to health', function () {
    expect(testFixture('page', BakeryInsights::class)->activeTab)->toBe('health');
});

test('extended trials is empty array', function () {
    expect(testFixture('page', BakeryInsights::class)->extendedTrials)->toBeArray()->toBeEmpty();
});

test('sent nudges is empty array', function () {
    expect(testFixture('page', BakeryInsights::class)->sentNudges)->toBeArray()->toBeEmpty();
});

test('get next plan', function () {
    expect(testFixture('page', BakeryInsights::class)->getNextPlan('starter'))->toBe('Growth')->and(testFixture('page', BakeryInsights::class)->getNextPlan('growth'))->toBe('Pro')->and(testFixture('page', BakeryInsights::class)->getNextPlan('pro'))->toBeNull();
});

test('plan limits constant exists', function () {
    expect(config('kneadit.plans'))->toHaveKeys(['starter', 'growth', 'pro'])->and(config('kneadit.plans.pro.limits.products'))->toBeNull()->and(config('kneadit.plans.starter.limits.products'))->toBe(25);
});

test('get health summary stats returns expected keys', function () {
    $result = testFixture('page', BakeryInsights::class)->getHealthSummaryStats();

    expect($result)->toHaveKeys(['average', 'healthy', 'at_risk', 'critical', 'total']);
});

test('get health summary stats with no tenants', function () {
    $result = testFixture('page', BakeryInsights::class)->getHealthSummaryStats();
    expect($result)->toMatchArray(['average' => 0, 'total' => 0]);
});

// Note: getHealthSummaryStats with tenants triggers tenancy()->initialize()
// which requires actual tenant databases. Testing with tenants would require
// full multi-tenancy setup, so we test the no-tenant path and properties only.
