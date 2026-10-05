<?php

declare(strict_types=1);

namespace App\Enums\Financial;

use Filament\Support\Contracts\HasLabel;

enum StripeConnectStatus: string implements HasLabel
{
    case NotConnected = 'not_connected';
    case ChargesPending = 'charges_pending';
    case ChargesEnabled = 'charges_enabled';

    public function getLabel(): string
    {
        return match ($this) {
            self::NotConnected => 'Not connected',
            self::ChargesPending => 'Connected, charges not enabled yet',
            self::ChargesEnabled => 'Connected, charges enabled',
        };
    }
}
