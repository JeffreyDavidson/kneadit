<?php

declare(strict_types=1);

namespace App\Services\Scheduling;

use App\Services\Notifications\ScheduledNotificationRunTracker;
use App\Services\Settings\TenantSettings;
use Closure;
use Throwable;

/**
 * Gates a per-tenant send on the bakery-local clock. Call run() from inside a
 * tenant's context with that tenant's settings: the send runs only when the
 * bakery's local time matches the schedule, and at most once per local date.
 * The once-per-date marker lives in the tenant's scheduled_notification_runs
 * table and is released again if the send throws, so a retry can still run.
 */
final readonly class LocalSendWindow
{
    public function __construct(
        private ScheduledNotificationRunTracker $runTracker,
    ) {}

    /**
     * @param  Closure(): void  $send
     */
    public function run(LocalSendSchedule $schedule, TenantSettings $settings, bool $force, Closure $send): void
    {
        if ($force) {
            $send();

            return;
        }

        $bakeryNow = new BakeryClock($settings)->now();

        if (! $schedule->isDue($bakeryNow)) {
            return;
        }

        $marker = $schedule->markerKey($bakeryNow);

        if (! $this->runTracker->claim($marker)) {
            return;
        }

        try {
            $send();
        } catch (Throwable $exception) {
            $this->runTracker->release($marker);

            throw $exception;
        }
    }
}
