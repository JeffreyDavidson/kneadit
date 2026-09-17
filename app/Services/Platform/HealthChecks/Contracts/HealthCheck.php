<?php

declare(strict_types=1);

namespace App\Services\Platform\HealthChecks\Contracts;

use App\Services\Platform\HealthChecks\HealthCheckResult;

interface HealthCheck
{
    public function run(): HealthCheckResult;
}
