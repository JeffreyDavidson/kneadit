<?php

namespace App\Enums\Financial;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Number;

enum CouponType: string implements HasColor, HasLabel
{
    case Percentage = 'percentage';
    case Fixed = 'fixed';

    public function getColor(): string
    {
        return match ($this) {
            self::Percentage => 'primary',
            self::Fixed => 'success',
        };
    }

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
