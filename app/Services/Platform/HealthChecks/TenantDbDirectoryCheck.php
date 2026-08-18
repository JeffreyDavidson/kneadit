<?php

namespace App\Services\Platform\HealthChecks;

use App\Services\Platform\HealthChecks\Contracts\HealthCheck;

class TenantDbDirectoryCheck implements HealthCheck
{
    public function run(): HealthCheckResult
    {
        $path = config('tenancy.tenant_db_path', database_path());

        if (! is_dir($path) || ! is_writable($path)) {
            return HealthCheckResult::fail("Tenant DB directory not writable: {$path}");
        }

        $count = count(glob("{$path}/*.sqlite") ?: []);

        return HealthCheckResult::pass("Tenant DB directory writable ({$count} databases)");
    }
}
