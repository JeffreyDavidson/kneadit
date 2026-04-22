<?php

namespace App\Enums\Operations;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BlockedDateReason: string implements HasColor, HasLabel
{
    case Vacation = 'Vacation';
    case Holiday = 'Holiday';
    case Maintenance = 'Maintenance';
    case Personal = 'Personal';
    case Other = 'Other';

    public function getLabel(): string
    {
        return $this->value;
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Vacation => 'info',
            self::Holiday => 'warning',
            self::Maintenance => 'danger',
            default => 'gray',
        };
    }
}
