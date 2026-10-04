<?php

declare(strict_types=1);

namespace App\Enums\Platform;

use Filament\Support\Contracts\HasLabel;

enum DomainCheck: string implements HasLabel
{
    case Verified = 'verified';
    case VerifiedThroughProxy = 'proxied';
    case DnsMissing = 'dns';
    case HttpsUnavailable = 'https';

    public function getLabel(): string
    {
        return match ($this) {
            self::Verified => 'Verified',
            self::VerifiedThroughProxy => 'Verified, served through a proxy',
            self::DnsMissing => 'DNS is not pointing at the server',
            self::HttpsUnavailable => 'No valid HTTPS certificate yet',
        };
    }

    public function isVerified(): bool
    {
        return match ($this) {
            self::Verified, self::VerifiedThroughProxy => true,
            self::DnsMissing, self::HttpsUnavailable => false,
        };
    }

    public function isProxied(): bool
    {
        return $this === self::VerifiedThroughProxy;
    }

    /**
     * Whether the domain reaches this application, directly or through a proxy.
     */
    public function dnsPointsHere(): bool
    {
        return $this !== self::DnsMissing;
    }
}
