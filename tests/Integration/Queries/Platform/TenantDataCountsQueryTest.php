<?php

use App\Models\Platform\Tenant;
use App\Queries\Platform\TenantDataCountsQuery;
use App\Services\Tenants\TenancyManager;
use JMac\Testing\Double;

beforeEach(fn () => setUpCentralTest());

test('counts tenant data inside the tenant context', function () {
    $tenant = Tenant::factory()->create();

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')
        ->resolves(fn (Tenant $tenant, callable $callback): mixed => $callback($tenant));

    app()->instance(TenancyManager::class, $tenancyManager);

    expect(resolve(TenantDataCountsQuery::class)->forTenant($tenant))
        ->toBe([
            'products' => 0,
            'categories' => 0,
            'orders' => 0,
            'customers' => 0,
            'reviews' => 0,
        ]);
});
