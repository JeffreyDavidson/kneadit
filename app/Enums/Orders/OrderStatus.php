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

    public function hexColor(): string
    {
        return match ($this) {
            self::Pending => '#F59E0B',
            self::Confirmed => '#3B82F6',
            self::Baking => '#8B5E3C',
            self::Ready => '#10B981',
            self::Delivered => '#6B7280',
            self::Cancelled => '#EF4444',
        };
    }

    public function hexBackground(): string
    {
        return match ($this) {
            self::Pending => '#FEF3C7',
            self::Confirmed => '#DBEAFE',
            self::Baking => '#F5E6D3',
            self::Ready => '#D1FAE5',
            self::Delivered => '#F3F4F6',
            self::Cancelled => '#FEE2E2',
        };
    }

    /** @return array<string, mixed> */
    public function toFunnelStage(int $count = 0): array
    {
        return [
            'key' => $this->value,
            'label' => $this->getLabel(),
            'color' => $this->hexColor(),
            'bg' => $this->hexBackground(),
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
