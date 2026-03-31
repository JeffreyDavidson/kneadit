<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Number;

enum CouponType: string implements HasLabel
{
    case Percentage = 'percentage';
    case Fixed = 'fixed';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }

    public function formatDiscount(float $value): string
    {
        return match ($this) {
            self::Percentage => Number::format($value) . '% off',
            self::Fixed => Number::currency($value) . ' off',
        };
    }
}
