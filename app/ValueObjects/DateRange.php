<?php

namespace App\ValueObjects;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;

final readonly class DateRange
{
    public function __construct(
        public Carbon $start,
        public Carbon $end,
    ) {}

    public static function fromStrings(string $startDate, string $endDate): self
    {
        return new self(
            Date::parse($startDate)->startOfDay(),
            Date::parse($endDate)->endOfDay(),
        );
    }

    public static function thisWeek(): self
    {
        return new self(
            Date::now()->startOfWeek(),
            Date::now()->endOfWeek(),
        );
    }

    public static function thisMonth(): self
    {
        return new self(
            Date::now()->startOfMonth(),
            Date::now()->endOfMonth(),
        );
    }

    public static function thisYear(): self
    {
        return new self(
            Date::now()->startOfYear(),
            Date::now()->endOfYear(),
        );
    }

    public static function lastDays(int $days): self
    {
        return new self(
            Date::now()->subDays($days)->startOfDay(),
            Date::now()->endOfDay(),
        );
    }

    public static function forMonth(int $year, int $month): self
    {
        $start = Date::create($year, $month, 1)->startOfMonth();

        return new self(
            $start,
            $start->copy()->endOfMonth(),
        );
    }

    /** @return array{0: Carbon, 1: Carbon} */
    public function toArray(): array
    {
        return [$this->start, $this->end];
    }
}
