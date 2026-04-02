<?php

namespace App\Enums\Financial;

enum PricingPosition: string
{
    case Economy = 'economy';
    case Standard = 'standard';
    case Premium = 'premium';

    public function multiplier(): float
    {
        return match ($this) {
            self::Economy => 0.85,
            self::Standard => 1.0,
            self::Premium => 1.25,
        };
    }
}
