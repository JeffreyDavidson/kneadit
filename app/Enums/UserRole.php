<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasLabel
{
    case Staff = 'staff';
    case Manager = 'manager';
    case Owner = 'owner';
    case PlatformAdmin = 'platform_admin';

    public function getLabel(): string
    {
        return match ($this) {
            self::Staff => 'Staff',
            self::Manager => 'Manager',
            self::Owner => 'Owner',
            self::PlatformAdmin => 'Platform Admin',
        };
    }

    public function level(): int
    {
        return match ($this) {
            self::Staff => 1,
            self::Manager => 2,
            self::Owner => 3,
            self::PlatformAdmin => 4,
        };
    }

    public function meetsRequirement(self $required): bool
    {
        return $this->level() >= $required->level();
    }
}
