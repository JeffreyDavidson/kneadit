<?php

declare(strict_types=1);

namespace App\Services\Platform;

use App\Services\Platform\Contracts\DnsResolver;

final readonly class PhpDnsResolver implements DnsResolver
{
    public function ipv4(string $domain): ?string
    {
        $ip = gethostbyname($domain);

        // gethostbyname() hands the name back unchanged when it cannot resolve it.
        if ($ip === $domain) {
            return null;
        }

        return $ip;
    }
}
