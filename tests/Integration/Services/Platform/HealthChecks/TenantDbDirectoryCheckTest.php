<?php

use App\Models\Platform\Tenant;
use App\Services\Platform\HealthChecks\TenantDbDirectoryCheck;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    setUpCentralTest();

    $this->tenantDbDirectory = storage_path('framework/testing/health-tenant-databases');
    File::ensureDirectoryExists($this->tenantDbDirectory);
    config(['tenancy.tenant_db_path' => $this->tenantDbDirectory]);
});

afterEach(function () {
    File::deleteDirectory($this->tenantDbDirectory);
});

test('tenant database health uses the configured directory', function () {
    $tenant = Tenant::withoutEvents(fn (): Tenant => Tenant::factory()->create(['id' => 'healthy-tenant']));
    $databaseName = (string) $tenant->database()->getName();
    File::put("{$this->tenantDbDirectory}/{$databaseName}", 'tenant database');

    $result = resolve(TenantDbDirectoryCheck::class)->run();

    expect($result->passed)->toBeTrue()
        ->and($result->message)->toBe('Tenant DB directory writable (1 databases)');
});

test('tenant database health fails when a configured tenant database is missing', function () {
    Tenant::withoutEvents(fn (): Tenant => Tenant::factory()->create(['id' => 'missing-tenant']));

    $result = resolve(TenantDbDirectoryCheck::class)->run();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toBe('Tenant DB directory contains 0 of 1 databases');
});
