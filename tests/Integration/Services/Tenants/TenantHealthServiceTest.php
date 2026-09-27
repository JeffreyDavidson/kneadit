<?php

use App\DataTransferObjects\Platform\TenantHealthMetrics;
use App\DataTransferObjects\Settings\BrandingSettings;
use App\Models\Platform\Tenant;
use App\Queries\Platform\TenantInsightsMetricsQuery;
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

it('returns zero summary stats when no tenants', function () {
    $service = resolve(TenantHealthService::class);
    $stats = $service->getHealthSummaryStats();

    expect($stats['average'])->toBe(0)
        ->and($stats['total'])->toBe(0)
        ->and($stats['healthy'])->toBe(0)
        ->and($stats['at_risk'])->toBe(0)
        ->and($stats['critical'])->toBe(0);
});

test('maps tenant health metrics into scored rows and summary statistics', function () {
    $healthyTenant = Tenant::factory()->make([
        'id' => 'healthy-bakery',
        'name' => 'Healthy Owner',
        'email' => 'owner@healthy-bakery.test',
        'store_name' => 'Healthy Bakery',
        'store_logo' => 'healthy-logo.png',
        'storefront_enabled' => true,
        'brand_color_primary' => '#123456',
    ]);
    $criticalTenant = Tenant::factory()->make([
        'id' => 'critical-bakery',
        'name' => 'Critical Owner',
        'email' => 'owner@critical-bakery.test',
        'store_name' => null,
        'store_logo' => null,
        'storefront_enabled' => false,
        'brand_color_primary' => BrandingSettings::DEFAULT_BRAND_COLOR,
    ]);

    $metricsQuery = Double::for(TenantInsightsMetricsQuery::class);
    $metricsQuery->expects('health')
        ->times(1)
        ->returns(collect([
            new TenantHealthMetrics(
                tenant: $healthyTenant,
                lastUserActivityAt: now()->subDay()->toDateTimeString(),
                totalOrders: 50,
                totalProducts: 20,
                totalCategories: 1,
            ),
            new TenantHealthMetrics(
                tenant: $criticalTenant,
                lastUserActivityAt: null,
                totalOrders: 0,
                totalProducts: 0,
                totalCategories: 0,
            ),
        ]));
    app()->instance(TenantInsightsMetricsQuery::class, $metricsQuery);

    $snapshot = resolve(TenantHealthService::class)->getTenantHealthSnapshot();

    expect($snapshot['tenants']->all())->toMatchArray([
        [
            'id' => 'critical-bakery',
            'name' => 'Critical Owner',
            'owner' => 'Critical Owner',
            'email' => 'owner@critical-bakery.test',
            'plan' => 'starter',
            'health_score' => 0,
            'login_score' => 0,
            'order_score' => 0,
            'product_score' => 0,
            'setup_score' => 0,
        ],
        [
            'id' => 'healthy-bakery',
            'name' => 'Healthy Bakery',
            'owner' => 'Healthy Owner',
            'email' => 'owner@healthy-bakery.test',
            'plan' => 'starter',
            'health_score' => 100,
            'login_score' => 25,
            'order_score' => 25,
            'product_score' => 20,
            'setup_score' => 30,
        ],
    ])->and($snapshot['stats'])->toBe([
        'average' => 50.0,
        'healthy' => 1,
        'at_risk' => 0,
        'critical' => 1,
        'total' => 2,
    ]);
});
