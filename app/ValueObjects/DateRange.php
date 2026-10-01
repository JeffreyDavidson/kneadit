<?php

namespace App\ValueObjects;

use App\Services\Scheduling\BakeryClock;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;

final readonly class DateRange
{
    public function __construct(
        public Carbon $start,
        public Carbon $end,
    ) {}

    /**
     * Whole bakery-local calendar days. Like every factory here, the range is
     * built in the bakery timezone, so it reads as those days when compared
     * with a date column; call inAppTimezone() before querying a timestamp.
     */
    public static function fromStrings(string $startDate, string $endDate): self
    {
        $timezone = self::now()->getTimezone();

        return new self(
            Date::parse($startDate, $timezone)->startOfDay(),
            Date::parse($endDate, $timezone)->endOfDay(),
        );
    }

    public static function thisWeek(): self
    {
        return new self(
            self::now()->startOfWeek(),
            self::now()->endOfWeek(),
        );
    }

    public static function thisMonth(): self
    {
        return new self(
            self::now()->startOfMonth(),
            self::now()->endOfMonth(),
        );
    }

    public static function thisYear(): self
    {
        return new self(
            self::now()->startOfYear(),
            self::now()->endOfYear(),
        );
    }

    public static function lastDays(int $days): self
    {
        return new self(
            self::now()->subDays($days)->startOfDay(),
            self::now()->endOfDay(),
        );
    }

    public static function forMonth(int $year, int $month): self
    {
        $start = Date::create($year, $month, 1, 0, 0, 0, self::now()->getTimezone())->startOfMonth();

        return new self(
            $start,
            $start->copy()->endOfMonth(),
        );
    }

    /**
     * The same instants expressed in the app timezone, which is how timestamp
     * columns are stored, so a range built from bakery-local boundaries can be
     * bound straight into a query.
     */
    public function inAppTimezone(): self
    {
        $timezone = Config::string('app.timezone');

        return new self(
            $this->start->copy()->setTimezone($timezone),
            $this->end->copy()->setTimezone($timezone),
        );
    }

    /**
     * Check if the current moment falls within this date range.
     */
    public function isActive(): bool
    {
        $now = Date::now();

        return $now->greaterThanOrEqualTo($this->start) && $now->lessThanOrEqualTo($this->end);
    }

    /**
     * Check if a given date falls within this range.
     */
    public function contains(Carbon|string $date): bool
    {
        $date = $date instanceof Carbon ? $date : Date::parse($date);

        return $date->greaterThanOrEqualTo($this->start) && $date->lessThanOrEqualTo($this->end);
    }

    /** @return array{0: Carbon, 1: Carbon} */
    public function toArray(): array
    {
        return [$this->start, $this->end];
    }

    private static function now(): Carbon
    {
        return resolve(BakeryClock::class)->now();
    }
}
