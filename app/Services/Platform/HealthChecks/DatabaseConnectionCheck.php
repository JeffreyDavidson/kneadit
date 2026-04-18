<?php

namespace App\Services\Platform\HealthChecks;

use App\Services\Platform\HealthChecks\Contracts\HealthCheck;
use Illuminate\Support\Facades\DB;

class DatabaseConnectionCheck implements HealthCheck
{
    public function run(): HealthCheckResult
    {
        try {
            DB::connection()->getPdo();

            return HealthCheckResult::pass('Database connection OK');
        } catch (\Exception $e) {
            return HealthCheckResult::fail("Database connection failed: {$e->getMessage()}");
        }
    }
}
