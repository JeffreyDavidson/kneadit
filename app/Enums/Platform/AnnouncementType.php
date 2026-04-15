<?php

namespace App\Enums\Platform;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AnnouncementType: string implements HasColor, HasLabel
{
    case Info = 'info';
    case Warning = 'warning';
    case Success = 'success';
    case Holiday = 'holiday';
    case Maintenance = 'maintenance';

    public function getColor(): string
    {
        return match ($this) {
            self::Info => 'info',
            self::Warning => 'warning',
            self::Success => 'success',
            self::Holiday => 'danger',
            self::Maintenance => 'gray',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Info => 'Info (Gold)',
            self::Warning => 'Warning (Orange)',
            self::Success => 'Success (Green)',
            self::Holiday => 'Holiday (Festive Red/Green)',
            self::Maintenance => 'Maintenance',
        };
    }
}
