<?php

namespace App\Enums\Orders;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum DeliveryDistanceTier: string implements HasColor, HasLabel
{
    case Close = 'close';
    case Medium = 'medium';
    case Far = 'far';

    public function getLabel(): string
    {
        return $this->name;
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Close => 'success',
            self::Medium => 'warning',
            self::Far => 'danger',
        };
    }

    public function estimatedMinutes(): int
    {
        return match ($this) {
            self::Close => 10,
            self::Medium => 20,
            self::Far => 35,
        };
    }
}
