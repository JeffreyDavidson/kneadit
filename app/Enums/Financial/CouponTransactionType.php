<?php

namespace App\Enums\Financial;

use Filament\Support\Contracts\HasLabel;

enum CouponTransactionType: string implements HasLabel
{
    case Usage = 'usage';
    case Reversal = 'reversal';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}
