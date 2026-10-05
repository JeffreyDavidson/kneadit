<?php

declare(strict_types=1);

namespace App\Services\Platform\HealthChecks;

use App\Services\Platform\HealthChecks\Contracts\HealthCheck;

class DiskSpaceCheck implements HealthCheck
{
    private const int GIGABYTE = 1073741824;

    /** Logging, SQLite and Redis had already failed on a full disk well before it was down to 1 GB. */
    private const int MINIMUM_FREE_GB = 5;

    private const int MINIMUM_FREE_PERCENT = 20;

    public function run(): HealthCheckResult
    {
        $free = $this->freeBytes();
        $total = $this->totalBytes();
        $freeGb = round($free / self::GIGABYTE, 1);
        $freePercent = $total > 0 ? round($free / $total * 100) : 0;

        if ($freeGb < self::MINIMUM_FREE_GB || $freePercent < self::MINIMUM_FREE_PERCENT) {
            return HealthCheckResult::fail("Low disk space: {$freeGb} GB free ({$freePercent}% of the disk)");
        }

        return HealthCheckResult::pass("Disk space OK ({$freeGb} GB free, {$freePercent}% of the disk)");
    }

    protected function freeBytes(): int
    {
        return (int) (disk_free_space(base_path()) ?: 0);
    }

    protected function totalBytes(): int
    {
        return (int) (disk_total_space(base_path()) ?: 0);
    }
}
