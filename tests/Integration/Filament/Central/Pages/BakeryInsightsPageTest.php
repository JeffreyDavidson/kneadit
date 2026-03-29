<?php

use App\Filament\Central\Pages\BakeryInsights;

beforeEach(function () {
    setUpCentralTest();
    test()->page = new BakeryInsights;
});

test('active tab defaults to health', function () {
    expect(test()->page->activeTab)->toBe('health');
});

test('extended trials is empty array', function () {
    expect(test()->page->extendedTrials)->toBeArray()->toBeEmpty();
});

test('sent nudges is empty array', function () {
    expect(test()->page->sentNudges)->toBeArray()->toBeEmpty();
});

test('get next plan', function () {
    expect(test()->page->getNextPlan('starter'))->toBe('Growth')->and(test()->page->getNextPlan('growth'))->toBe('Pro')->and(test()->page->getNextPlan('pro'))->toBeNull();
});

test('plan limits constant exists', function () {
    expect(config('kneadit.plans'))->toHaveKeys(['starter', 'growth', 'pro'])->and(config('kneadit.plans.pro.limits.products'))->toBeNull()->and(config('kneadit.plans.starter.limits.products'))->toBe(25);
});

test('get health summary stats returns expected keys', function () {
    $result = test()->page->getHealthSummaryStats();

    expect($result)->toHaveKeys(['average', 'healthy', 'at_risk', 'critical', 'total']);
});

test('get health summary stats with no tenants', function () {
    $result = test()->page->getHealthSummaryStats();
    expect($result)->toMatchArray(['average' => 0, 'total' => 0]);
});

// Note: getHealthSummaryStats with tenants triggers tenancy()->initialize()
// which requires actual tenant databases. Testing with tenants would require
// full multi-tenancy setup, so we test the no-tenant path and properties only.
