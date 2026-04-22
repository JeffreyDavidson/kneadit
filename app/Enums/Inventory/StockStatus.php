<?php

namespace App\Enums\Inventory;

use App\Models\Inventory\Ingredient;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StockStatus: string implements HasColor, HasLabel
{
    case Good = 'good';
    case Low = 'low';
    case Out = 'out';

    public static function resolve(Ingredient $ingredient): self
    {
        if ($ingredient->current_stock <= 0) {
            return self::Out;
        }
        if ($ingredient->current_stock <= $ingredient->low_stock_threshold) {
            return self::Low;
        }

        return self::Good;
    }

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
