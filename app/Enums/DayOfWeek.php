<?php

namespace App\Enums;

enum DayOfWeek: int
{
    case Monday = 0;
    case Tuesday = 1;
    case Wednesday = 2;
    case Thursday = 3;
    case Friday = 4;
    case Saturday = 5;
    case Sunday = 6;

    public function label(): string
    {
        return $this->name;
    }

    /**
     * @return array<int, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $day) => [(string) $day->value => $day->label()])
            ->all();
    }
}
