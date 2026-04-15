<?php

namespace App\Enums\Financial;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum GiftCardStatus: string implements HasColor, HasLabel
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Expired = 'expired';
    case Depleted = 'depleted';

    public function getColor(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Expired => 'danger',
            self::Depleted => 'warning',
            self::Inactive => 'gray',
        };
    }

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}
