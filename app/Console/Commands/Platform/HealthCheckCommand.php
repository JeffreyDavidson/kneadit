<?php

namespace App\Console\Commands\Platform;

use App\Events\Platform\HealthCheckFailed;
use App\Services\Platform\HealthChecks\Contracts\HealthCheck;
use App\Services\Platform\HealthChecks\DatabaseConnectionCheck;
use App\Services\Platform\HealthChecks\DiskSpaceCheck;
use App\Services\Platform\HealthChecks\HomepageRespondsCheck;
use App\Services\Platform\HealthChecks\StorageLogsCheck;
use App\Services\Platform\HealthChecks\TenantDbDirectoryCheck;
use App\Services\Platform\HealthChecks\UsersTableCheck;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('health:check')]
#[Description('Run platform health checks and alert on failures')]
class HealthCheckCommand extends Command
{
    /** @var array<int, class-string<HealthCheck>> */
    private const array CHECKS = [
        DatabaseConnectionCheck::class,
        UsersTableCheck::class,
        TenantDbDirectoryCheck::class,
        DiskSpaceCheck::class,
        StorageLogsCheck::class,
        HomepageRespondsCheck::class,
    ];

    public function handle(): int
    {
        $issues = [];

        foreach (self::CHECKS as $class) {
            $result = resolve($class)->run();

            if ($result->passed) {
                $this->info('✓ ' . $result->message);
            } else {
                $this->error('✗ ' . $result->message);
                $issues[] = $result->message;
            }
        }

        if (! empty($issues)) {
            $this->alertOnIssues($issues);

            return Command::FAILURE;
        }

        $this->info("\nAll health checks passed.");

        return Command::SUCCESS;
    }

    /** @param array<int, string> $issues */
    protected function alertOnIssues(array $issues): void
    {
        $issueText = implode("\n- ", $issues);
        $message = "KneadIt Health Check Alert\n\nIssues detected:\n- {$issueText}\n\nTime: " . now()->toDateTimeString();

        Log::critical('Health check failed', ['issues' => $issues]);

        event(new HealthCheckFailed($message));
        $this->info('Health check alert dispatched.');
    }
}
