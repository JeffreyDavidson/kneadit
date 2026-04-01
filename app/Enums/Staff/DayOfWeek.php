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
}
