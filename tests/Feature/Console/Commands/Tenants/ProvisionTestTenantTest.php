<?php

use App\Models\Platform\Tenant;
use App\Services\Tenants\TenantSQLiteDatabaseManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Stancl\Tenancy\Database\Models\Domain;

test('browser test tenant can use a DNS-free domain', function () {
    test()->artisan('migrate:fresh', ['--database' => 'central'])->assertSuccessful();

    $tenantDatabasePath = storage_path('framework/testing/browser-test-tenant-databases-' . getmypid());
    File::ensureDirectoryExists($tenantDatabasePath);
    config(['tenancy.tenant_db_path' => $tenantDatabasePath]);

    try {
        $orphanedTenant = new Tenant(['id' => 'browser-test']);

        expect(resolve(TenantSQLiteDatabaseManager::class)->createDatabase($orphanedTenant))->toBeTrue();

        test()->artisan('tenants:provision-test-tenant', ['--fresh' => true, '--domain' => '[::1]'])
            ->expectsOutputToContain('Dropping orphaned browser-test tenant database')
            ->expectsOutputToContain('browser-test tenant ready at http://[::1]')
            ->assertSuccessful();

        expect(Tenant::query()->whereKey('browser-test')->exists())->toBeTrue()
            ->and(Domain::query()->where('domain', '[::1]')->exists())->toBeTrue();
    } finally {
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        DB::purge('tenant');
        File::deleteDirectory($tenantDatabasePath);
    }
});
