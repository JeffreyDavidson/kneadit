<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasLabel
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
