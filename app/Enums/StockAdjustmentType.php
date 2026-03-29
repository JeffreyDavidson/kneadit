<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum StockAdjustmentType: string implements HasLabel
{
    case Purchase = 'purchase';
    case Usage = 'usage';
    case Adjustment = 'adjustment';
    case Waste = 'waste';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}
