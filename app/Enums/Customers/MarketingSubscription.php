<?php

namespace App\Enums\Customers;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Carbon;

enum MarketingSubscription: string implements HasColor, HasLabel
{
    case Subscribed = 'subscribed';
    case Unsubscribed = 'unsubscribed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Subscribed => 'Subscribed',
            self::Unsubscribed => 'Unsubscribed',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Subscribed => 'success',
            self::Unsubscribed => 'gray',
        };
    }

    /**
     * The label with the opt-out date appended, e.g. "Unsubscribed since Sep 20, 2026".
     */
    public function labelSince(?Carbon $optedOutAt): string
    {
        if ($this === self::Subscribed || ! $optedOutAt instanceof Carbon) {
            return $this->getLabel();
        }

        return "{$this->getLabel()} since {$optedOutAt->format('M j, Y')}";
    }

    public static function resolve(?Carbon $optedOutAt): self
    {
        return $optedOutAt instanceof Carbon
            ? self::Unsubscribed
            : self::Subscribed;
    }
}
