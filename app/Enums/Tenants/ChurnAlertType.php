<?php

namespace App\Enums\Tenants;

use Filament\Support\Contracts\HasLabel;

enum ChurnAlertType: string implements HasLabel
{
    case TrialExpiring = 'trial_expiring';
    case NoLogin = 'no_login';
    case NoOrders = 'no_orders';
    case LowHealth = 'low_health';

    public function getLabel(): string
    {
        return match ($this) {
            self::TrialExpiring => 'Trial Expiring',
            self::NoLogin => 'No Recent Login',
            self::NoOrders => 'No Orders',
            self::LowHealth => 'Critical Health',
        };
    }
}
