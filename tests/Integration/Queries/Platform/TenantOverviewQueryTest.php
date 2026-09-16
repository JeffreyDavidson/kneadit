<?php

use App\DataTransferObjects\Platform\TenantOverviewMetrics;
use App\Models\Platform\Tenant;
use App\Queries\Platform\TenantOverviewQuery;
use App\Services\Tenants\TenancyManager;
use JMac\Testing\Double;

beforeEach(fn () => setUpCentralTest());

test('returns typed overview metrics from the tenant database', function () {
    $tenant = Tenant::factory()->create();

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')
        ->resolves(fn (Tenant $receivedTenant, callable $callback): mixed => $callback($receivedTenant));

    app()->instance(TenancyManager::class, $tenancyManager);

    $overview = resolve(TenantOverviewQuery::class)->forTenant($tenant);

    expect($overview)
        ->toBeInstanceOf(TenantOverviewMetrics::class)
        ->and($overview->products)->toBe(0)
        ->and($overview->categories)->toBe(0)
        ->and($overview->orders)->toBe(0)
        ->and($overview->customers)->toBe(0)
        ->and($overview->reviews)->toBe(0)
        ->and($overview->revenue)->toBe(0.0)
        ->and($overview->lastOrder)->toBeNull();
});
