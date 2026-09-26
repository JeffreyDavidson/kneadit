<?php

use App\Models\Platform\Tenant;
use App\Queries\Platform\TenantInsightsMetricsQuery;
use App\Services\Tenants\TenancyManager;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Log;
use JMac\Testing\Double;
use JMac\Testing\Matching\Argument;

beforeEach(fn () => setUpCentralTest());

test('omits tenants whose health metric reads fail and logs the failure', function () {
    $tenant = Tenant::factory()->create();

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')
        ->times(1)
        ->throws(new RuntimeException('Tenant database unavailable'));

    app()->instance(TenancyManager::class, $tenancyManager);

    Log::shouldReceive('warning')->once()->with('Unable to calculate tenant health', [
        'tenant_id' => $tenant->id,
        'error' => 'Tenant database unavailable',
    ]);

    expect(resolve(TenantInsightsMetricsQuery::class)->health())->toBeEmpty();
});

test('collects health and churn metrics inside one tenant context per tenant', function () {
    $tenants = Tenant::factory()->count(2)->create()->sortBy('id')->values();

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')
        ->times(4)
        ->with(
            Argument::satisfies(fn (mixed $tenant): bool => $tenant instanceof Tenant),
            Argument::satisfies(fn (mixed $callback): bool => is_callable($callback)),
        )
        ->resolves(fn (Tenant $tenant, callable $callback): mixed => $callback($tenant));

    app()->instance(TenancyManager::class, $tenancyManager);

    $query = resolve(TenantInsightsMetricsQuery::class);
    $health = $query->health();
    $churn = $query->churn(noOrdersDays: 30, minimumTenantAgeDays: 14);

    expect($health)->toHaveCount(2)
        ->and($churn)->toHaveCount(2)
        ->and($health->first()->tenant->id)->toBe($tenants->first()->id)
        ->and($churn->first()->tenant->id)->toBe($tenants->first()->id)
        ->and($churn->first()->healthMetrics)->not->toBeNull()
        ->and($churn->first()->recentOrderCount)->toBeNull();
});

test('keeps recent order count unavailable when an eligible tenant read fails', function () {
    $tenant = Tenant::factory()->create([
        'created_at' => now()->subDays(30),
    ]);

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')
        ->times(1)
        ->resolves(fn (Tenant $tenant, callable $callback): mixed => $callback($tenant));

    app()->instance(TenancyManager::class, $tenancyManager);

    DB::listen(function (QueryExecuted $query): void {
        if (str_contains($query->sql, '"orders"') && str_contains($query->sql, '"created_at"')) {
            throw new RuntimeException('Recent order read failed');
        }
    });

    Log::shouldReceive('warning')->once()->with('Unable to evaluate tenant order churn', [
        'tenant_id' => $tenant->id,
        'error' => 'Recent order read failed',
    ]);

    $metrics = resolve(TenantInsightsMetricsQuery::class)
        ->churn(noOrdersDays: 30, minimumTenantAgeDays: 14)
        ->first();

    expect($metrics->healthMetrics)->not->toBeNull()
        ->and($metrics->recentOrderCount)->toBeNull();
});

test('continues reading churn order counts when health metrics fail', function () {
    $tenant = Tenant::factory()->create([
        'created_at' => now()->subDays(30),
    ]);

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')
        ->times(1)
        ->resolves(fn (Tenant $tenant, callable $callback): mixed => $callback($tenant));

    app()->instance(TenancyManager::class, $tenancyManager);

    DB::listen(function (QueryExecuted $query): void {
        if (str_contains($query->sql, 'users') && str_contains($query->sql, 'updated_at')) {
            throw new RuntimeException('Health read failed');
        }
    });

    Log::shouldReceive('warning')->once()->with('Unable to calculate tenant health', [
        'tenant_id' => $tenant->id,
        'error' => 'Health read failed',
    ]);

    $metrics = resolve(TenantInsightsMetricsQuery::class)
        ->churn(noOrdersDays: 30, minimumTenantAgeDays: 14)
        ->first();

    expect($metrics->healthMetrics)->toBeNull()
        ->and($metrics->recentOrderCount)->toBe(0);
});
