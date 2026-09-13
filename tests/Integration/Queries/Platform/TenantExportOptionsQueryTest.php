<?php

use App\Models\Platform\Tenant;
use App\Queries\Platform\TenantExportOptionsQuery;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

beforeEach(fn () => setUpCentralTest());

test('returns tenants ordered for the export selector', function () {
    $zebra = Tenant::factory()->create(['store_name' => 'Zebra Bakery']);
    $alpha = Tenant::factory()->create(['store_name' => 'Alpha Bakery']);
    $fallback = Tenant::factory()->create(['store_name' => null, 'name' => 'Fallback Name']);

    $options = resolve(TenantExportOptionsQuery::class)->all();

    expect(array_keys($options))->toBe([$fallback->id, $alpha->id, $zebra->id])
        ->and(array_values($options))->toBe(['Fallback Name', 'Alpha Bakery', 'Zebra Bakery']);
});

test('iterates tenant options in bounded chunks', function () {
    Tenant::factory()->count(101)->create();
    $tenantQueries = [];

    DB::listen(function (QueryExecuted $query) use (&$tenantQueries): void {
        if (str_contains(strtolower($query->sql), 'from "tenants"')) {
            $tenantQueries[] = $query->sql;
        }
    });

    $options = resolve(TenantExportOptionsQuery::class)->all();

    expect($options)->toHaveCount(101)
        ->and($tenantQueries)->toHaveCount(2)
        ->and($tenantQueries[0])->toContain('limit 100')
        ->and($tenantQueries[1])->toContain('limit 100');
});
