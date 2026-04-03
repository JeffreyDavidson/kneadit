<?php

namespace App\Enums\Financial;

use Filament\Support\Contracts\HasLabel;

enum PricingPosition: string implements HasLabel
{
    case Economy = 'economy';
    case Standard = 'standard';
    case Premium = 'premium';

    public function getLabel(): string
    {
        return match ($this) {
            self::Economy => 'Economy',
            self::Standard => 'Standard',
            self::Premium => 'Premium',
        };
    }

    public function multiplier(): float
    {
        return match ($this) {
            self::Economy => 0.85,
            self::Standard => 1.0,
            self::Premium => 1.25,
        };
    }
}
