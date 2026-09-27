<?php

use App\Models\Orders\Order;
use App\Models\Platform\Tenant;
use App\Queries\Platform\TenantComparisonMetricsQuery;
use App\Services\Tenants\TenancyManager;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use JMac\Testing\Double;

beforeEach(fn () => setUpCentralTest());

test('reads monthly order metrics once from each requested tenant database', function () {
    $monthStart = now()->startOfMonth();
    $tenant = Tenant::factory()->create([
        'store_name' => 'Metrics Bakery',
    ]);
    $otherTenant = Tenant::factory()->create([
        'store_name' => 'Other Bakery',
    ]);

    $tenancyManager = resolve(TenancyManager::class);
    $tenancyManager->withinTenant($tenant, function () use ($monthStart): void {
        Order::factory()->create(['created_at' => $monthStart->copy()->subSecond()]);
        Order::factory()->create(['created_at' => $monthStart->copy()]);
        Order::factory()->create(['created_at' => now()]);
        Order::factory()->create(['created_at' => now()->addDay()]);
        Order::factory()->cancelled()->unpaid()->create([
            'created_at' => $monthStart->copy()->addDay(),
        ]);
    });
    $tenancyManager->withinTenant($otherTenant, function () use ($monthStart): void {
        Order::factory()->create(['created_at' => $monthStart->copy()->subDay()]);
    });

    $orderConnections = [];
    DB::listen(function (QueryExecuted $query) use (&$orderConnections): void {
        $sql = strtolower(str_replace(['"', '`', '[', ']'], '', $query->sql));

        if (str_contains($sql, 'from orders')) {
            $orderConnections[] = $query->connectionName;
        }
    });

    $query = resolve(TenantComparisonMetricsQuery::class);
    $metrics = $query->forTenant($tenant);
    $otherMetrics = $query->forTenant($otherTenant);

    expect($metrics->id)->toBe($tenant->id)
        ->and($metrics->name)->toBe('Metrics Bakery')
        ->and($metrics->totalOrders)->toBe(5)
        ->and($metrics->monthOrders)->toBe(4)
        ->and($otherMetrics->totalOrders)->toBe(1)
        ->and($otherMetrics->monthOrders)->toBe(0)
        ->and($orderConnections)->toBe(['tenant', 'tenant']);
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

test('returns zeroed metrics when a tenant order query fails', function () {
    $tenant = Tenant::factory()->create();

    DB::listen(function (QueryExecuted $query): void {
        $sql = strtolower(str_replace(['"', '`', '[', ']'], '', $query->sql));

        if (str_contains($sql, 'from orders')) {
            throw new RuntimeException('Order metrics unavailable');
        }
    });

    $metrics = resolve(TenantComparisonMetricsQuery::class)->forTenant($tenant);

    expect($metrics->totalOrders)->toBe(0)
        ->and($metrics->monthOrders)->toBe(0)
        ->and($metrics->totalProducts)->toBe(0)
        ->and($metrics->totalCategories)->toBe(0)
        ->and($metrics->avgReview)->toBe(0.0);
});
