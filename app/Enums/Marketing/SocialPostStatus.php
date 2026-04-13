<?php

namespace App\Enums\Marketing;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum SocialPostStatus: string implements HasColor, HasIcon, HasLabel
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Posted = 'posted';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Draft => Heroicon::OutlinedPencilSquare,
            self::Scheduled => Heroicon::OutlinedClock,
            self::Posted => Heroicon::OutlinedCheckCircle,
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Scheduled => 'warning',
            self::Posted => 'success',
        };
    }
}
