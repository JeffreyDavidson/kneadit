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
