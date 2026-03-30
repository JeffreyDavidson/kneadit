<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum LoyaltyPointType: string implements HasLabel
{
    case Earned = 'earned';
    case Redeemed = 'redeemed';
    case Adjusted = 'adjusted';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}
