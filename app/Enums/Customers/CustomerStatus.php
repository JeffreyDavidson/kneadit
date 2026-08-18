<?php

namespace App\Enums\Customers;

use Carbon\Carbon;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CustomerStatus: string implements HasColor, HasLabel
{
    case Active = 'active';
    case AtRisk = 'at_risk';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::AtRisk => 'At Risk',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::AtRisk => 'danger',
        };
    }

    /**
     * "At risk" = customer has placed at least one order, but their most
     * recent one is older than the threshold (config: analytics.at_risk_threshold_days,
     * default 30). Customers with no orders are reported as Active — they're
     * leads, not at-risk customers.
     */
    public static function resolve(int $orderCount, ?Carbon $lastOrderDate, ?int $thresholdDays = null): self
    {
        if ($orderCount <= 0 || ! $lastOrderDate) {
            return self::Active;
        }

        $threshold = $thresholdDays ?? (int) (string) config('analytics.at_risk_threshold_days', 30);

        return $lastOrderDate->diffInDays(now()) > $threshold
            ? self::AtRisk
            : self::Active;
    }
}
