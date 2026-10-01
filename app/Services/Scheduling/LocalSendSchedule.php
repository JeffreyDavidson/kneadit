<?php

declare(strict_types=1);

namespace App\Services\Scheduling;

use Carbon\WeekDay;
use Illuminate\Support\Carbon;

/**
 * When a tenant-facing scheduled send is due, on the bakery's own clock: a
 * target hour, optionally on one weekday only. The scheduler runs the command
 * hourly and each tenant sends only when its local time matches.
 */
final readonly class LocalSendSchedule
{
    public function __construct(
        public string $command,
        public int $hour,
        public ?WeekDay $weekDay = null,
    ) {}

    public function isDue(Carbon $bakeryNow): bool
    {
        if ($bakeryNow->hour !== $this->hour) {
            return false;
        }

        return ! $this->weekDay instanceof WeekDay || $bakeryNow->dayOfWeek === $this->weekDay->value;
    }

    /**
     * One marker per command per bakery-local date.
     */
    public function markerKey(Carbon $bakeryNow): string
    {
        return "local-send:{$this->command}:{$bakeryNow->toDateString()}";
    }
}
