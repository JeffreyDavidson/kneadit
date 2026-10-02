<?php

declare(strict_types=1);

namespace App\Enums\Platform;

use Filament\Support\Contracts\HasLabel;

enum DomainCheck: string implements HasLabel
{
    case Verified = 'verified';
    case DnsMissing = 'dns';
    case HttpsUnavailable = 'https';

    public function getLabel(): string
    {
        return match ($this) {
            self::Verified => 'Verified',
            self::DnsMissing => 'DNS is not pointing at the server',
            self::HttpsUnavailable => 'No valid HTTPS certificate yet',
        };
    }

    public function isVerified(): bool
    {
        return $this === self::Verified;
    }

    public function dnsPointsHere(): bool
    {
        return $this !== self::DnsMissing;
    }
}
