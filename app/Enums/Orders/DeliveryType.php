<?php

namespace App\Enums\Orders;

use Filament\Support\Contracts\HasLabel;

enum DeliveryType: string implements HasLabel
{
    case Pickup = 'pickup';
    case Delivery = 'delivery';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}
