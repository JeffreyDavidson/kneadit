<?php

namespace App\Services\Platform\HealthChecks;

use App\Models\Platform\Tenant;
use App\Services\Platform\HealthChecks\Contracts\HealthCheck;
use App\Services\Tenants\TenantDatabasePath;
use Illuminate\Support\Facades\Config;

class TenantDbDirectoryCheck implements HealthCheck
{
    public function __construct(private readonly TenantDatabasePath $tenantDatabasePath) {}

    public function run(): HealthCheckResult
    {
        $path = Config::string('tenancy.tenant_db_path', database_path());

        if (! is_dir($path) || ! is_writable($path)) {
            return HealthCheckResult::fail("Tenant DB directory not writable: {$path}");
        }

        $tenants = Tenant::all();
        $count = $tenants->filter(function (Tenant $tenant): bool {
            $databaseName = (string) $tenant->database()->getName();
            $path = $this->tenantDatabasePath->resolve($databaseName);

            return is_file($path) && ! is_link($path);
        })->count();

        if ($count !== $tenants->count()) {
            return HealthCheckResult::fail("Tenant DB directory contains {$count} of {$tenants->count()} databases");
        }

        return HealthCheckResult::pass("Tenant DB directory writable ({$count} databases)");
    }
}
