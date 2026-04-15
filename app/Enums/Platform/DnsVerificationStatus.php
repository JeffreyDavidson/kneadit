<?php

namespace App\Enums\Platform;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum DnsVerificationStatus: string implements HasColor, HasLabel
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

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Verified => 'success',
        };
    }
}
