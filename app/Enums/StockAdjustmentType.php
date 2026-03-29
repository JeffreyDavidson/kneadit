<?php

namespace App\Enums;

enum StockAdjustmentType: string
{
    case Purchase = 'purchase';
    case Usage = 'usage';
    case Adjustment = 'adjustment';
    case Waste = 'waste';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
