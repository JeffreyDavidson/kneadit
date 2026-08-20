<?php

use App\Filament\Central\Pages\BakeryInsights;

beforeEach(function () {
    setUpCentralTest();
});

test('page class has required methods', function () {
    expect(BakeryInsights::class)->toHaveMethods([
        'getTenantHealthData',
        'getAlerts',
        'getTenantUsageData',
        'getHealthSummaryStats',
    ]);
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
