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

    /** How the sender is named to a bakery reading its Messages page. */
    public function inboxLabel(): string
    {
        return match ($this) {
            self::Admin => 'KneadIt Team',
            self::Tenant => 'You',
        };
    }

    public function inboxIcon(): string
    {
        return match ($this) {
            self::Admin => 'heroicon-o-shield-check',
            self::Tenant => 'heroicon-o-building-storefront',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Admin => 'info',
            self::Tenant => 'warning',
        };
    }
}
