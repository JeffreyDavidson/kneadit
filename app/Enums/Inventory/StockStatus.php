<?php

namespace App\Enums\Inventory;

use Filament\Support\Contracts\HasLabel;

enum StockStatus: string implements HasLabel
{
    case Good = 'good';
    case Low = 'low';
    case Out = 'out';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}
