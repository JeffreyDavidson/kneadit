<?php

namespace App\Services\Platform\HealthChecks;

use App\Services\Platform\HealthChecks\Contracts\HealthCheck;

class StorageLogsCheck implements HealthCheck
{
    public function run(): HealthCheckResult
    {
        $path = storage_path('logs');

        if (! is_dir($path)) {
            return HealthCheckResult::fail('Storage/logs directory does not exist');
        }

        if (! is_writable($path)) {
            return HealthCheckResult::fail('Storage/logs directory not writable');
        }

        return HealthCheckResult::pass('Storage/logs writable');
    }
}
