<?php

use App\Filament\Central\Pages\BakeryInsights;

beforeEach(function () {
    setUpCentralTest();
});

test('page class has required methods', function () {
    $page = new BakeryInsights;

    expect(method_exists($page, 'getTenantHealthData'))->toBeTrue()->and(method_exists($page, 'getAlerts'))->toBeTrue()->and(method_exists($page, 'getTenantUsageData'))->toBeTrue()->and(method_exists($page, 'getHealthSummaryStats'))->toBeTrue();
});

test('health summary returns expected keys', function () {
    $page = new BakeryInsights;
    $stats = $page->getHealthSummaryStats();

    expect($stats)->toHaveKeys(['average', 'healthy', 'at_risk', 'critical', 'total']);
});

test('get next plan returns correct upgrades', function () {
    $page = new BakeryInsights;

    expect($page->getNextPlan('starter'))->toBe('Growth')->and($page->getNextPlan('growth'))->toBe('Pro')->and($page->getNextPlan('pro'))->toBeNull();
});

test('plan limits constant exists', function () {
    expect(config('kneadit.plans'))->toHaveKeys(['starter', 'growth', 'pro']);
});
