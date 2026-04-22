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

    public function bgClass(): string
    {
        return match ($this) {
            self::Info => 'bg-blue-500/15',
            self::Warning => 'bg-honey/15',
            self::Success => 'bg-emerald-500/15',
            self::Holiday => 'bg-gradient-to-br from-[#c41e3a] to-[#1a6b2a]',
            self::Maintenance => 'bg-gray-500/15',
        };
    }

    public function textClass(): string
    {
        return match ($this) {
            self::Info => 'text-blue-500',
            self::Warning => 'text-honey',
            self::Success => 'text-emerald-500',
            self::Holiday => 'text-white',
            self::Maintenance => 'text-gray-500',
        };
    }

    public function borderClass(): string
    {
        return match ($this) {
            self::Info => 'border-blue-500/25',
            self::Warning => 'border-honey/25',
            self::Success => 'border-emerald-500/25',
            self::Holiday => 'border-[#ffd700]',
            self::Maintenance => 'border-gray-500/25',
        };
    }
}
