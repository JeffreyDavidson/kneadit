<?php

namespace App\Enums\Tenants;

enum ChurnAlertType: string
{
    case TrialExpiring = 'trial_expiring';
    case NoLogin = 'no_login';
    case NoOrders = 'no_orders';
    case LowHealth = 'low_health';

    public function label(): string
    {
        return match ($this) {
            self::TrialExpiring => 'Trial Expiring',
            self::NoLogin => 'No Recent Login',
            self::NoOrders => 'No Orders',
            self::LowHealth => 'Critical Health',
        };
    }
}
