<?php

use App\Models\Platform\Tenant;
use App\Queries\Platform\TenantOverviewQuery;
use App\Services\Tenants\TenancyManager;
use JMac\Testing\Double;

beforeEach(fn () => setUpCentralTest());

test('returns the tenant overview metrics required by the admin page', function () {
    $tenant = Tenant::factory()->create();

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')
        ->resolves(fn (Tenant $receivedTenant, callable $callback): mixed => $callback($receivedTenant));

    app()->instance(TenancyManager::class, $tenancyManager);

    $overview = resolve(TenantOverviewQuery::class)->adminStats($tenant);

    expect($overview)
        ->toBe([
            'products' => 0,
            'orders' => 0,
            'revenue' => 0.0,
            'customers' => 0,
            'reviews' => 0,
            'last_order' => null,
        ]);
});
