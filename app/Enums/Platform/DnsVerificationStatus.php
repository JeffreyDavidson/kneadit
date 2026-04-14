<?php

namespace App\Enums\Platform;

use Filament\Support\Contracts\HasLabel;

enum DnsVerificationStatus: string implements HasLabel
{
    case Verified = 'verified';
    case Pending = 'pending';

    public function getLabel(): string
    {
        return match ($this) {
            self::Verified => 'Verified',
            self::Pending => 'Pending',
        };
    }
}
