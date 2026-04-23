<?php

namespace App\Enums\Customers;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum RfmSegment: string implements HasColor, HasLabel
{
    case Champions = 'champions';
    case Loyal = 'loyal';
    case New = 'new';
    case AtRisk = 'at_risk';
    case Hibernating = 'hibernating';

    public function getLabel(): string
    {
        return match ($this) {
            self::Champions => 'Champions',
            self::Loyal => 'Loyal',
            self::New => 'New',
            self::AtRisk => 'At Risk',
            self::Hibernating => 'Hibernating',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Champions => 'success',
            self::Loyal => 'info',
            self::New => 'warning',
            self::AtRisk => 'danger',
            self::Hibernating => 'gray',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Champions => 'Ordered recently, frequently, and at a high value. Reward and retain.',
            self::Loyal => 'Consistent customers on track to become Champions. Nurture.',
            self::New => 'First order within the last 30 days. Welcome warmly.',
            self::AtRisk => 'Previously valuable but haven\'t ordered in 60–180 days. Re-engage.',
            self::Hibernating => 'Low engagement and/or 180+ days since last order. Consider a win-back offer.',
        };
    }
}
