<?php

namespace App\Enums\Platform;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum PlatformEventType: string implements HasColor, HasIcon, HasLabel
{
    case TenantCreated = 'tenant_created';
    case TenantDeactivated = 'tenant_deactivated';
    case PlanChanged = 'plan_changed';
    case StorefrontToggled = 'storefront_toggled';
    case TrialExpired = 'trial_expired';

    public function getLabel(): string
    {
        return match ($this) {
            self::TenantCreated => 'Tenant Created',
            self::TenantDeactivated => 'Tenant Deactivated',
            self::PlanChanged => 'Plan Changed',
            self::StorefrontToggled => 'Storefront Toggled',
            self::TrialExpired => 'Trial Expired',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::TenantCreated => '#d4920c',
            self::TenantDeactivated => '#ef4444',
            self::PlanChanged => '#e8b04a',
            self::StorefrontToggled => '#8b6844',
            self::TrialExpired => '#f5d88e',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::TenantCreated => Heroicon::OutlinedPlusCircle,
            self::TenantDeactivated => Heroicon::OutlinedXCircle,
            self::PlanChanged => Heroicon::OutlinedArrowPath,
            self::StorefrontToggled => Heroicon::OutlinedGlobeAlt,
            self::TrialExpired => Heroicon::OutlinedClock,
        };
    }
}
