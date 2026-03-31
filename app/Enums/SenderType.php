<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SenderType: string implements HasLabel
{
    case Baker = 'baker';
    case Customer = 'customer';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }

    public function isBaker(): bool
    {
        return $this === self::Baker;
    }

    public function isCustomer(): bool
    {
        return $this === self::Customer;
    }
}
