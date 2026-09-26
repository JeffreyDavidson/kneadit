<?php

use App\Models\Platform\Tenant;
use App\Queries\Platform\TenantOverviewQuery;
use App\Services\Tenants\TenancyManager;
use JMac\Testing\Double;

beforeEach(function () {
    setUpCentralTest();
});

it('returns tenant statistics from the tenant database', function () {
    $tenant = Tenant::factory()->create();
    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')
        ->resolves(fn (Tenant $tenant, callable $callback): mixed => $callback($tenant));

    app()->instance(TenancyManager::class, $tenancyManager);

    expect(resolve(TenantOverviewQuery::class)->adminStats($tenant))
        ->toBe([
            'products' => 0,
            'orders' => 0,
            'revenue' => 0.0,
            'customers' => 0,
            'reviews' => 0,
            'last_order' => null,
        ]);
});
