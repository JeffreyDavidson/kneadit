<?php

declare(strict_types=1);

namespace App\Services\Platform\HealthChecks;

use App\Services\Platform\HealthChecks\Contracts\HealthCheck;
use App\Services\Platform\ScheduledTaskMonitor;
use Carbon\CarbonInterface;

/**
 * Fails when the scheduler has gone quiet. Something is scheduled at least every
 * 15 minutes, so a gap of over an hour means the scheduler is not running.
 */
class SchedulerHeartbeatCheck implements HealthCheck
{
    private const int MAXIMUM_SILENCE_MINUTES = 70;

    public function __construct(
        private readonly ScheduledTaskMonitor $monitor,
    ) {}

    public function run(): HealthCheckResult
    {
        $lastStartedAt = $this->monitor->lastStartedAt();

        if (! $lastStartedAt instanceof CarbonInterface) {
            return HealthCheckResult::fail('Scheduler silent: no scheduled task has started yet');
        }

        if ($lastStartedAt->lt(now()->subMinutes(self::MAXIMUM_SILENCE_MINUTES))) {
            return HealthCheckResult::fail("Scheduler silent: no scheduled task has started since {$lastStartedAt->format('Y-m-d H:i')}");
        }

        return HealthCheckResult::pass("Scheduler running (last task started {$lastStartedAt->format('Y-m-d H:i')})");
    }
}
