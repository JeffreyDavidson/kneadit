<?php

namespace App\Enums\Engagement;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum LoyaltyTier: string implements HasColor, HasLabel
{
    case Bronze = 'bronze';
    case Silver = 'silver';
    case Gold = 'gold';
    case Platinum = 'platinum';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Bronze => 'warning',
            self::Silver => 'gray',
            self::Gold => 'success',
            self::Platinum => 'info',
        };
    }

    /** @return array<int, self> */
    public static function ordered(): array
    {
        return [self::Bronze, self::Silver, self::Gold, self::Platinum];
    }

    public function next(): ?self
    {
        return match ($this) {
            self::Bronze => self::Silver,
            self::Silver => self::Gold,
            self::Gold => self::Platinum,
            self::Platinum => null,
        };
    }
}
