<?php

namespace App\Services\Platform\HealthChecks;

use App\Services\Platform\HealthChecks\Contracts\HealthCheck;
use Illuminate\Support\Facades\Config;

class TenantDbDirectoryCheck implements HealthCheck
{
    public function run(): HealthCheckResult
    {
        $path = Config::string('tenancy.tenant_db_path', database_path());

        if (! is_dir($path) || ! is_writable($path)) {
            return HealthCheckResult::fail("Tenant DB directory not writable: {$path}");
        }

        $count = count(glob("{$path}/*.sqlite") ?: []);

        return HealthCheckResult::pass("Tenant DB directory writable ({$count} databases)");
    }
}
