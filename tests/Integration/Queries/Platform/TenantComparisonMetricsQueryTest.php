<?php

use App\Models\Platform\Tenant;
use App\Queries\Platform\TenantComparisonMetricsQuery;
use App\Services\Tenants\TenancyManager;
use JMac\Testing\Double;
use JMac\Testing\Matching\Argument;

beforeEach(fn () => setUpCentralTest());

test('collects comparison metrics inside the requested tenant context', function () {
    $tenant = Tenant::factory()->create([
        'store_name' => 'Metrics Bakery',
    ]);

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')
        ->with($tenant, Argument::satisfies(fn (mixed $callback): bool => is_callable($callback)))
        ->resolves(fn (Tenant $receivedTenant, callable $callback): mixed => $callback($receivedTenant));

    app()->instance(TenancyManager::class, $tenancyManager);

    $metrics = resolve(TenantComparisonMetricsQuery::class)->forTenant($tenant);

    expect($metrics->id)->toBe($tenant->id)
        ->and($metrics->name)->toBe('Metrics Bakery')
        ->and($metrics->totalOrders)->toBe(0)
        ->and($metrics->monthOrders)->toBe(0)
        ->and($metrics->totalProducts)->toBe(0)
        ->and($metrics->totalCategories)->toBe(0)
        ->and($metrics->avgReview)->toBe(0.0);
});

test('returns zeroed metrics when tenant data cannot be read', function () {
    $tenant = Tenant::factory()->create();

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')->throws(new RuntimeException('Tenant unavailable'));

    app()->instance(TenancyManager::class, $tenancyManager);

    $metrics = resolve(TenantComparisonMetricsQuery::class)->forTenant($tenant);

    expect($metrics->totalOrders)->toBe(0)
        ->and($metrics->monthOrders)->toBe(0)
        ->and($metrics->totalProducts)->toBe(0)
        ->and($metrics->totalCategories)->toBe(0)
        ->and($metrics->avgReview)->toBe(0.0);
});
