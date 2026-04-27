<?php

namespace App\Enums\Orders;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum OrderStatus: string implements HasColor, HasIcon, HasLabel
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
            self::Pending => 'warning',
            self::Confirmed => 'info',
            self::Baking => 'primary',
            self::Ready => 'success',
            self::Delivered => 'gray',
            self::Cancelled => 'danger',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Pending => Heroicon::OutlinedClock,
            self::Confirmed => Heroicon::OutlinedCheckCircle,
            self::Baking => Heroicon::OutlinedFire,
            self::Ready => Heroicon::OutlinedCube,
            self::Delivered => Heroicon::OutlinedTruck,
            self::Cancelled => Heroicon::OutlinedXCircle,
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending => 'bg-yellow-100 text-yellow-800',
            self::Confirmed => 'bg-green-100 text-green-800',
            self::Baking => 'bg-blue-100 text-blue-800',
            self::Ready => 'bg-emerald-100 text-emerald-800',
            self::Delivered => 'bg-gray-100 text-gray-800',
            self::Cancelled => 'bg-red-100 text-red-800',
        };
    }

    public function funnelDotColor(): string
    {
        return match ($this) {
            self::Pending => '#e8b04a',
            self::Confirmed => '#3a8bd4',
            self::Baking => '#8b5e3c',
            self::Ready => '#6b9e3a',
            self::Delivered => '#a08060',
            self::Cancelled => '#d4574a',
        };
    }

    /** @return array<string, mixed> */
    public function toFunnelStage(int $count = 0): array
    {
        return [
            'key' => $this->value,
            'label' => $this->getLabel(),
            'dotColor' => $this->funnelDotColor(),
            'count' => $count,
        ];
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
