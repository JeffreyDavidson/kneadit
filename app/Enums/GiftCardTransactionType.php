<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum GiftCardTransactionType: string implements HasLabel
{
    case Purchase = 'purchase';
    case Redemption = 'redemption';
    case Refund = 'refund';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}
