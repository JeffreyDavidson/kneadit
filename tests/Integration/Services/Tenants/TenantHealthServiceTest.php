<?php

use App\Services\Tenants\TenantHealthService;

beforeEach(function () {
    setUpCentralTest();
});

it('returns health summary stats with correct keys', function () {
    $service = resolve(TenantHealthService::class);
    $stats = $service->getHealthSummaryStats();

    expect($stats)->toHaveKeys(['average', 'healthy', 'at_risk', 'critical', 'total']);
});

it('returns empty health data when no tenants exist', function () {
    $service = resolve(TenantHealthService::class);
    $data = $service->getTenantHealthData();

    expect($data)->toBeEmpty();
});

it('returns zero summary stats when no tenants', function () {
    $service = resolve(TenantHealthService::class);
    $stats = $service->getHealthSummaryStats();

    expect($stats['average'])->toBe(0)
        ->and($stats['total'])->toBe(0)
        ->and($stats['healthy'])->toBe(0)
        ->and($stats['at_risk'])->toBe(0)
        ->and($stats['critical'])->toBe(0);
});
