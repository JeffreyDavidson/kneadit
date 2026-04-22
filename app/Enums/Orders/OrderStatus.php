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

    public function funnelTextClass(): string
    {
        return match ($this) {
            self::Pending => 'text-amber-500',
            self::Confirmed => 'text-blue-500',
            self::Baking => 'text-brand-600',
            self::Ready => 'text-emerald-500',
            self::Delivered => 'text-gray-500',
            self::Cancelled => 'text-red-500',
        };
    }

    public function funnelBgClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-100',
            self::Confirmed => 'bg-blue-100',
            self::Baking => 'bg-brand-100',
            self::Ready => 'bg-emerald-100',
            self::Delivered => 'bg-gray-100',
            self::Cancelled => 'bg-red-100',
        };
    }

    public function funnelBorderClass(): string
    {
        return match ($this) {
            self::Pending => 'border-amber-500',
            self::Confirmed => 'border-blue-500',
            self::Baking => 'border-brand-600',
            self::Ready => 'border-emerald-500',
            self::Delivered => 'border-gray-500',
            self::Cancelled => 'border-red-500',
        };
    }

    /** @return array<string, mixed> */
    public function toFunnelStage(int $count = 0): array
    {
        return [
            'key' => $this->value,
            'label' => $this->getLabel(),
            'textClass' => $this->funnelTextClass(),
            'bgClass' => $this->funnelBgClass(),
            'borderClass' => $this->funnelBorderClass(),
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
