<?php

namespace App\Enums\Platform;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AnnouncementType: string implements HasColor, HasLabel
{
    case Info = 'info';
    case Warning = 'warning';
    case Success = 'success';
    case Maintenance = 'maintenance';

    public function getColor(): string
    {
        return match ($this) {
            self::Info => 'info',
            self::Warning => 'warning',
            self::Success => 'success',
            self::Maintenance => 'gray',
        };
    }

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}
