<?php

namespace App\Console\Commands\Platform;

use App\Mail\Platform\HealthAlertMail;
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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

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

    /** How long a check that keeps failing stays quiet after it has alerted. */
    private const int ALERT_COOLDOWN_HOURS = 6;

    public function handle(): int
    {
        $issues = [];

        foreach (self::CHECKS as $class) {
            $result = resolve($class)->run();

            if ($result->passed) {
                $this->info('✓ '.$result->message);
                $this->notifyIfRecovered($class, $result->message);

                continue;
            }

            $this->error('✗ '.$result->message);
            $issues[$class] = $result->message;
        }

        if ($issues !== []) {
            $this->alertOnIssues($issues);

            return Command::FAILURE;
        }

        $this->info("\nAll health checks passed.");

        return Command::SUCCESS;
    }

    /**
     * Mails the checks that are not already inside their alert cooldown. The
     * mail is sent in-process rather than queued, because the central database
     * or disk that a queued alert would depend on may be the thing that is failing.
     *
     * @param  array<class-string<HealthCheck>, string>  $issues
     */
    protected function alertOnIssues(array $issues): void
    {
        Log::critical('Health check failed', ['issues' => array_values($issues)]);

        $due = array_filter($issues, fn (string $message, string $class): bool => ! $this->isCoolingDown($class), ARRAY_FILTER_USE_BOTH);

        if ($due === []) {
            $this->info('Health check alert suppressed (already alerted within the last '.self::ALERT_COOLDOWN_HOURS.' hours).');

            return;
        }

        $issueText = implode("\n- ", $due);

        if (! $this->sendMail("KneadIt Health Check Alert\n\nIssues detected:\n- {$issueText}\n\nTime: ".now()->toDateTimeString(), '⚠️ KneadIt Health Check Alert')) {
            return;
        }

        foreach (array_keys($due) as $class) {
            $this->startCooldown($class);
        }

        $this->info('Health check alert sent.');
    }

    /** @param class-string<HealthCheck> $class */
    private function notifyIfRecovered(string $class, string $message): void
    {
        try {
            $wasAlerting = Cache::pull($this->cooldownKey($class)) !== null;
        } catch (Throwable) {
            return;
        }

        if (! $wasAlerting) {
            return;
        }

        $this->sendMail("KneadIt Health Check Recovered\n\n{$message}\n\nTime: ".now()->toDateTimeString(), '✅ KneadIt Health Check Recovered');
    }

    /** A mail that cannot be sent is logged; it must not hide the failing check that triggered it. */
    private function sendMail(string $message, string $subject): bool
    {
        $recipient = Config::get('mail.platform_notify');

        if (! is_string($recipient) || $recipient === '') {
            Log::error('Health alert not sent: no platform notification address configured');

            return false;
        }

        try {
            Mail::sendNow(new HealthAlertMail($message, $subject)->to($recipient));
        } catch (Throwable $exception) {
            Log::error('Health alert mail failed', ['error' => $exception->getMessage()]);

            return false;
        }

        return true;
    }

    /** If the cache is itself what is failing, the check counts as not cooling down so the alert still goes out. */
    private function isCoolingDown(string $class): bool
    {
        try {
            return Cache::has($this->cooldownKey($class));
        } catch (Throwable) {
            return false;
        }
    }

    private function startCooldown(string $class): void
    {
        try {
            Cache::put($this->cooldownKey($class), true, now()->addHours(self::ALERT_COOLDOWN_HOURS));
        } catch (Throwable $exception) {
            Log::warning('Health alert cooldown not recorded', ['error' => $exception->getMessage()]);
        }
    }

    private function cooldownKey(string $class): string
    {
        return "health-check-alert:{$class}";
    }
}
