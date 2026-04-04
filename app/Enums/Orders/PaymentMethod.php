<?php

namespace App\Enums\Orders;

use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasLabel
{
    case Cash = 'cash';
    case PayPal = 'paypal';
    case Stripe = 'stripe';
    case Other = 'other';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }

    public function isManual(): bool
    {
        return match ($this) {
            self::Cash, self::Other => true,
            self::Stripe, self::PayPal => false,
        };
    }
}
