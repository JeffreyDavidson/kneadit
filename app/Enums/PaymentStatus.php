<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentStatus: string implements HasLabel
{
    case Unpaid = 'unpaid';
    case Paid = 'paid';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}
