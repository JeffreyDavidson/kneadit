<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case PayPal = 'paypal';
    case Stripe = 'stripe';
    case Other = 'other';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
