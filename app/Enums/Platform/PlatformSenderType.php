<?php

namespace App\Enums\Platform;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PlatformSenderType: string implements HasColor, HasLabel
{
    case Admin = 'admin';
    case Tenant = 'tenant';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Admin => 'info',
            self::Tenant => 'warning',
        };
    }
}
