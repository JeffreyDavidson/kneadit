<?php

use App\Filament\Central\Pages\BakeryInsights;
use App\Services\Tenant\TenantHealthService;

beforeEach(function () {
    setUpCentralTest();
    test()->page = new BakeryInsights;
});

test('active tab defaults to health', function () {
    expect(test()->page->activeTab)->toBe('health');
});

test('extended trials is empty array', function () {
    expect(test()->page->extendedTrials)->toBeArray();
    expect(test()->page->extendedTrials)->toBeEmpty();
});

test('sent nudges is empty array', function () {
    expect(test()->page->sentNudges)->toBeArray();
    expect(test()->page->sentNudges)->toBeEmpty();
});

test('get next plan', function () {
    expect(test()->page->getNextPlan('starter'))->toBe('Growth');
    expect(test()->page->getNextPlan('growth'))->toBe('Pro');
    expect(test()->page->getNextPlan('pro'))->toBeNull();
});

test('plan limits constant exists', function () {
    expect(TenantHealthService::PLAN_LIMITS)->toHaveKey('starter');
    expect(TenantHealthService::PLAN_LIMITS)->toHaveKey('growth');
    expect(TenantHealthService::PLAN_LIMITS)->toHaveKey('pro');
    expect(TenantHealthService::PLAN_LIMITS['pro']['products'])->toBeNull();
    expect(TenantHealthService::PLAN_LIMITS['starter']['products'])->toBe(15);
});

test('get health summary stats returns expected keys', function () {
    $result = test()->page->getHealthSummaryStats();

    expect($result)->toHaveKey('average');
    expect($result)->toHaveKey('healthy');
    expect($result)->toHaveKey('at_risk');
    expect($result)->toHaveKey('critical');
    expect($result)->toHaveKey('total');
});

test('get health summary stats with no tenants', function () {
    $result = test()->page->getHealthSummaryStats();

    expect($result['average'])->toBe(0);
    expect($result['total'])->toBe(0);
});

// Note: getHealthSummaryStats with tenants triggers tenancy()->initialize()
// which requires actual tenant databases. Testing with tenants would require
// full multi-tenancy setup, so we test the no-tenant path and properties only.
