<?php

namespace App\Services\Platform\HealthChecks;

use App\Services\Platform\HealthChecks\Contracts\HealthCheck;

class DiskSpaceCheck implements HealthCheck
{
    private const int MINIMUM_FREE_GB = 1;

    public function run(): HealthCheckResult
    {
        $freeGb = round((disk_free_space(base_path()) ?: 0) / 1073741824, 1);

        if ($freeGb < self::MINIMUM_FREE_GB) {
            return HealthCheckResult::fail("Low disk space: {$freeGb} GB free");
        }

        return HealthCheckResult::pass("Disk space OK ({$freeGb} GB free)");
    }
}
