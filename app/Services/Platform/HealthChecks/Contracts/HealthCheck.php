<?php

namespace App\Services\Platform\HealthChecks\Contracts;

use App\Services\Platform\HealthChecks\HealthCheckResult;

interface HealthCheck
{
    public function run(): HealthCheckResult;
}
