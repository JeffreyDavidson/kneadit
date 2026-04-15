<?php

namespace App\Enums\Customers;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum WaitlistStatus: string implements HasColor, HasLabel
{
    case Waiting = 'waiting';
    case Notified = 'notified';
    case Converted = 'converted';
    case Removed = 'removed';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Waiting => 'warning',
            self::Notified => 'info',
            self::Converted => 'success',
            self::Removed => 'danger',
        };
    }
}
