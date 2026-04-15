<?php

namespace App\Enums\Inventory;

use Filament\Support\Contracts\HasLabel;

enum StockAdjustmentType: string implements HasLabel
{
    case Purchase = 'purchase';
    case Usage = 'usage';
    case Adjustment = 'adjustment';
    case Waste = 'waste';

    public function getLabel(): string
    {
        return match ($this) {
            self::Purchase => 'Purchase (add)',
            self::Usage => 'Usage (subtract)',
            self::Waste => 'Waste (subtract)',
            self::Adjustment => 'Adjustment',
        };
    }
}
