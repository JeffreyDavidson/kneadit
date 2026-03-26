<?php

use App\Filament\Central\Pages\BakeryInsights;
use App\Services\Tenant\TenantHealthService;

beforeEach(function () {
    setUpCentralTest();
});

test('page class has required methods', function () {
    $page = new BakeryInsights;

    expect(method_exists($page, 'getTenantHealthData'))->toBeTrue();
    expect(method_exists($page, 'getAlerts'))->toBeTrue();
    expect(method_exists($page, 'getTenantUsageData'))->toBeTrue();
    expect(method_exists($page, 'getHealthSummaryStats'))->toBeTrue();
});

test('health summary returns expected keys', function () {
    $page = new BakeryInsights;
    $stats = $page->getHealthSummaryStats();

    expect($stats)->toHaveKey('average');
    expect($stats)->toHaveKey('healthy');
    expect($stats)->toHaveKey('at_risk');
    expect($stats)->toHaveKey('critical');
    expect($stats)->toHaveKey('total');
});

test('get next plan returns correct upgrades', function () {
    $page = new BakeryInsights;

    expect($page->getNextPlan('starter'))->toBe('Growth');
    expect($page->getNextPlan('growth'))->toBe('Pro');
    expect($page->getNextPlan('pro'))->toBeNull();
});

test('plan limits constant exists', function () {
    expect(TenantHealthService::PLAN_LIMITS)->toHaveKey('starter');
    expect(TenantHealthService::PLAN_LIMITS)->toHaveKey('growth');
    expect(TenantHealthService::PLAN_LIMITS)->toHaveKey('pro');
});
