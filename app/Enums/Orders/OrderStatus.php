<?php

namespace App\Enums\Orders;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Baking = 'baking';
    case Ready = 'ready';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Confirmed => 'warning',
            self::Baking => 'info',
            self::Ready => 'success',
            self::Delivered => 'primary',
            self::Cancelled => 'danger',
        };
    }

    /** @return array<int, self> */
    public static function trackableStatuses(): array
    {
        return [
            self::Pending,
            self::Confirmed,
            self::Baking,
            self::Ready,
            self::Delivered,
        ];
    }
}
