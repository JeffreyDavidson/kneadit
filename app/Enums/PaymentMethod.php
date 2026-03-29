<?php

namespace App\Enums;

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
}
