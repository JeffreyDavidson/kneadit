<?php

namespace App\Enums\Financial;

use Filament\Support\Contracts\HasLabel;

enum GiftCardStatus: string implements HasLabel
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Expired = 'expired';
    case Depleted = 'depleted';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}
