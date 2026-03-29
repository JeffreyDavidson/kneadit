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
}
