<?php

declare(strict_types=1);

namespace App\Services\Platform\HealthChecks;

use App\Services\Platform\HealthChecks\Contracts\HealthCheck;
use App\Services\Platform\ScheduledTaskMonitor;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;

/**
 * Fails when database backups have stopped. `backup:databases` runs twice a day
 * (03:00 and 15:00), so a newest backup older than 13 hours means a run was missed,
 * and a last run that did not succeed means the newest backup may be incomplete.
 */
class DatabaseBackupCheck implements HealthCheck
{
    private const int MAXIMUM_AGE_HOURS = 13;

    private const string BACKUP_TASK = 'backup:databases';

    public function __construct(
        private readonly ScheduledTaskMonitor $monitor,
    ) {}

    public function run(): HealthCheckResult
    {
        $lastRun = $this->monitor->status(self::BACKUP_TASK);

        if (($lastRun['status'] ?? null) === 'failed') {
            $reason = is_int($lastRun['exit_code'] ?? null)
                ? "exit code {$lastRun['exit_code']}"
                : (is_string($lastRun['error'] ?? null) ? $lastRun['error'] : 'no exit code');

            return HealthCheckResult::fail("Database backup failed: the last scheduled run did not succeed ({$reason})");
        }

        $newest = $this->newestBackupAt();

        if (! $newest instanceof CarbonInterface) {
            return HealthCheckResult::fail('Database backup missing: no backups found');
        }

        if ($newest->lt(now()->subHours(self::MAXIMUM_AGE_HOURS))) {
            return HealthCheckResult::fail("Database backup stale: the newest backup is from {$newest->format('Y-m-d H:i')}");
        }

        return HealthCheckResult::pass("Database backup OK (newest {$newest->format('Y-m-d H:i')})");
    }

    /**
     * Completed backups are directories named by their start time. Unfinished
     * ones (`<name>.in-progress-<random>`) and stray files do not match.
     */
    private function newestBackupAt(): ?CarbonInterface
    {
        $newest = null;

        foreach (glob(Config::string('backups.path').'/20*', GLOB_ONLYDIR) ?: [] as $directory) {
            if (! preg_match('/^\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}$/', basename($directory))) {
                continue;
            }

            $startedAt = Date::createFromFormat('!Y-m-d_H-i-s', basename($directory));

            if (! $startedAt instanceof CarbonInterface) {
                continue;
            }

            if (! $newest instanceof CarbonInterface || $startedAt->gt($newest)) {
                $newest = $startedAt;
            }
        }

        return $newest;
    }
}
