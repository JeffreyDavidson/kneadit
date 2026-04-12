<?php

namespace App\Enums\Inventory;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StockStatus: string implements HasColor, HasLabel
{
    case Good = 'good';
    case Low = 'low';
    case Out = 'out';

    public function getLabel(): string
    {
        return match ($this) {
            self::Out => 'Out of Stock',
            default => ucfirst($this->value),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Good => 'success',
            self::Low => 'warning',
            self::Out => 'danger',
        };
    }
}
