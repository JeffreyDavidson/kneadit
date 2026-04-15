<?php

namespace App\Enums\Staff;

use Filament\Support\Contracts\HasLabel;

enum DayOfWeek: string implements HasLabel
{
    case Monday = 'monday';
    case Tuesday = 'tuesday';
    case Wednesday = 'wednesday';
    case Thursday = 'thursday';
    case Friday = 'friday';
    case Saturday = 'saturday';
    case Sunday = 'sunday';

    public function getLabel(): string
    {
        return $this->name;
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $day) => [$day->value => $day->getLabel()])
            ->all();
    }

    public static function fromPhpDayIndex(int $index): ?self
    {
        return match ($index) {
            0 => self::Sunday,
            1 => self::Monday,
            2 => self::Tuesday,
            3 => self::Wednesday,
            4 => self::Thursday,
            5 => self::Friday,
            6 => self::Saturday,
            default => null,
        };
    }

    /** @return array<int, self> PHP day-of-week order (0=Sunday) */
    public static function phpWeekOrder(): array
    {
        return [
            0 => self::Sunday,
            1 => self::Monday,
            2 => self::Tuesday,
            3 => self::Wednesday,
            4 => self::Thursday,
            5 => self::Friday,
            6 => self::Saturday,
        ];
    }
}
