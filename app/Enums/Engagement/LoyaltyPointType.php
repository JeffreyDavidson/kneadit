<?php

namespace App\Enums\Engagement;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum LoyaltyPointType: string implements HasColor, HasLabel
{
    case Earned = 'earned';
    case Redeemed = 'redeemed';
    case Adjusted = 'adjusted';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Earned => 'success',
            self::Redeemed => 'warning',
            self::Adjusted => 'info',
        };
    }
}
