<?php

namespace App\DataTransferObjects\Analytics;

use Carbon\Carbon;

final readonly class DateSeries
{
    /** @param list<string> $dates */
    public function __construct(private array $dates) {}

    /** @return list<string> */
    public function dates(): array
    {
        return $this->dates;
    }

    public static function between(Carbon $start, Carbon $end): self
    {
        $dates = [];
        $date = $start->copy()->startOfDay();

        while ($date->lte($end)) {
            $dates[] = $date->toDateString();
            $date->addDay();
        }

        return new self($dates);
    }

    /**
     * @param  array<string, int>  $values
     * @return list<int>
     */
    public function fillIntegers(array $values): array
    {
        return array_map(fn (string $date): int => $values[$date] ?? 0, $this->dates);
    }
}
