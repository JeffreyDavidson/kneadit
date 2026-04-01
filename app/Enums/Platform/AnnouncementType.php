<?php

namespace App\Enums\Platform;

use Filament\Support\Contracts\HasLabel;

enum AnnouncementType: string implements HasLabel
{
    case Info = 'info';
    case Warning = 'warning';
    case Success = 'success';
    case Maintenance = 'maintenance';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::Info => 'info',
            self::Warning => 'warning',
            self::Success => 'success',
            self::Maintenance => 'gray',
        };
    }
}
