<?php

namespace App\Enums\Engagement;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum LoyaltyPointType: string implements HasColor, HasLabel
{
    case Earned = 'earned';
    case Redeemed = 'redeemed';
    case Adjusted = 'adjusted';
    case Reversed = 'reversed';

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
            self::Reversed => 'danger',
        };
    }

    public function textClass(): string
    {
        return match ($this) {
            self::Earned => 'text-green-600',
            self::Redeemed => 'text-red-600',
            self::Adjusted => 'text-yellow-600',
            self::Reversed => 'text-red-600',
        };
    }

    /**
     * Format a stored points value for display, with its sign.
     *
     * Earned, redeemed and reversed rows store a positive magnitude, so the
     * sign comes from the type. Adjusted rows are stored signed, so the sign comes from
     * the value.
     */
    public function formatPoints(int $points): string
    {
        $sign = match ($this) {
            self::Earned => '+',
            self::Redeemed, self::Reversed => '-',
            self::Adjusted => $points < 0 ? '-' : '+',
        };

        return $sign.number_format(abs($points));
    }
}
