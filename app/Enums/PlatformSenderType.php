<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PlatformSenderType: string implements HasLabel
{
    case Admin = 'admin';
    case Tenant = 'tenant';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}
