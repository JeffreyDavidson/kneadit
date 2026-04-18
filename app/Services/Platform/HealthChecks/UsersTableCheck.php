<?php

namespace App\Services\Platform\HealthChecks;

use App\Services\Platform\HealthChecks\Contracts\HealthCheck;
use Illuminate\Support\Facades\DB;

class UsersTableCheck implements HealthCheck
{
    public function run(): HealthCheckResult
    {
        try {
            $count = DB::table('users')->count();

            return HealthCheckResult::pass("Users table OK ({$count} users)");
        } catch (\Exception $e) {
            return HealthCheckResult::fail("Users table query failed: {$e->getMessage()}");
        }
    }
}
