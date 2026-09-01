<?php

use App\Models\Platform\Tenant;
use App\Services\Tenants\TenancyManager;
use App\Services\Tenants\TenantHealthService;
use JMac\Testing\Double;

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

it('calculates health scores for tenants', function () {
    createTenant([
        'id' => 'health-test',
        'name' => 'Test Baker',
        'email' => 'health@test.com',
        'store_name' => 'Health Bakery',
        'store_logo' => 'logo.png',
        'storefront_enabled' => true,
        'brand_color_primary' => '#ff0000',
    ]);

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')
        ->resolves(function (Tenant $tenant, callable $callback): array {
            return [
                'days_since_login' => 1,
                'total_orders' => 50,
                'total_products' => 20,
                'has_products' => true,
                'has_categories' => true,
                'has_orders' => true,
            ];
        });

    app()->instance(TenancyManager::class, $tenancyManager);

    $service = resolve(TenantHealthService::class);
    $data = $service->getTenantHealthData();

    expect($data)->toHaveCount(1)
        ->and($data->first()['name'])->toBe('Health Bakery')
        ->and($data->first()['health_score'])->toBeGreaterThan(0);
});

it('handles tenant context failure gracefully', function () {
    createTenant([
        'id' => 'error-tenant',
        'name' => 'Error Baker',
        'email' => 'error@test.com',
    ]);

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')
        ->throws(new RuntimeException('DB connection failed'));

    app()->instance(TenancyManager::class, $tenancyManager);

    $service = resolve(TenantHealthService::class);
    $data = $service->getTenantHealthData();

    // Should still have tenant but with zero scores
    expect($data)->toHaveCount(1)
        ->and($data->first()['health_score'])->toBeGreaterThanOrEqual(0);
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

it('gets last login for a tenant', function () {
    $tenant = Tenant::factory()->create();

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')
        ->returns('2026-04-01 10:00:00');

    app()->instance(TenancyManager::class, $tenancyManager);

    $service = resolve(TenantHealthService::class);
    $result = $service->getLastLogin($tenant);

    expect($result)->toBe('2026-04-01 10:00:00');
});

it('returns null for last login when tenant context fails', function () {
    $tenant = Tenant::factory()->create();

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')
        ->throws(new RuntimeException('DB error'));

    app()->instance(TenancyManager::class, $tenancyManager);

    $service = resolve(TenantHealthService::class);
    $result = $service->getLastLogin($tenant);

    expect($result)->toBeNull();
});

it('gets recent order count for a tenant', function () {
    $tenant = Tenant::factory()->create();

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')
        ->returns(15);

    app()->instance(TenancyManager::class, $tenancyManager);

    $service = resolve(TenantHealthService::class);
    $result = $service->getRecentOrderCount($tenant, 30);

    expect($result)->toBe(15);
});

it('returns zero order count when tenant context fails', function () {
    $tenant = Tenant::factory()->create();

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')
        ->throws(new RuntimeException('DB error'));

    app()->instance(TenancyManager::class, $tenancyManager);

    $service = resolve(TenantHealthService::class);
    $result = $service->getRecentOrderCount($tenant, 30);

    expect($result)->toBe(0);
});
