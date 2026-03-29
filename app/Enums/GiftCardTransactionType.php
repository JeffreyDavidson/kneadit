<?php

namespace App\Enums;

enum GiftCardTransactionType: string
{
    case Purchase = 'purchase';
    case Redemption = 'redemption';
    case Refund = 'refund';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
