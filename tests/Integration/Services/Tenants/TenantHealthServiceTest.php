<?php

use App\Models\Platform\Tenant;
use App\Services\Tenants\TenancyManager;
use App\Services\Tenants\TenantHealthService;
use Illuminate\Support\Facades\Log;
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

it('omits unreadable tenants without preventing other health scores', function () {
    createTenant([
        'id' => 'error-tenant',
        'name' => 'Error Baker',
        'email' => 'error@test.com',
    ]);
    createTenant([
        'id' => 'healthy-tenant',
        'name' => 'Healthy Baker',
        'email' => 'healthy@test.com',
    ]);

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')
        ->times(2)
        ->resolves(function (Tenant $tenant): array {
            if ($tenant->id === 'error-tenant') {
                throw new RuntimeException('DB connection failed');
            }

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

    Log::shouldReceive('warning')
        ->once()
        ->with('Unable to calculate tenant health', [
            'tenant_id' => 'error-tenant',
            'error' => 'DB connection failed',
        ]);

    $service = resolve(TenantHealthService::class);
    $data = $service->getTenantHealthData();

    expect($data)->toHaveCount(1)
        ->and($data->first()['id'])->toBe('healthy-tenant');
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

it('preserves a successful zero recent order count', function () {
    $tenant = Tenant::factory()->create();

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')->returns(0);

    app()->instance(TenancyManager::class, $tenancyManager);

    expect(resolve(TenantHealthService::class)->getRecentOrderCount($tenant, 30))->toBe(0);
});

it('propagates recent order read failures', function () {
    $tenant = Tenant::factory()->create();

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')
        ->throws(new RuntimeException('DB error'));

    app()->instance(TenancyManager::class, $tenancyManager);

    expect(fn () => resolve(TenantHealthService::class)->getRecentOrderCount($tenant, 30))
        ->toThrow(RuntimeException::class, 'DB error');
});
