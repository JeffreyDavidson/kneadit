<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum RewardType: string implements HasLabel
{
    case PercentageDiscount = 'percentage_discount';
    case FixedDiscount = 'fixed_discount';
    case FreeProduct = 'free_product';

    public function getLabel(): string
    {
        return match ($this) {
            self::PercentageDiscount => 'Percentage Off',
            self::FixedDiscount => 'Fixed Discount',
            self::FreeProduct => 'Free Product',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PercentageDiscount => 'info',
            self::FixedDiscount => 'success',
            self::FreeProduct => 'warning',
        };
    }
}
