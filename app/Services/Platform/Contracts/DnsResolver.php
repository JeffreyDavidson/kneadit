<?php

declare(strict_types=1);

namespace App\Services\Platform\Contracts;

interface DnsResolver
{
    /**
     * The IPv4 address the domain currently resolves to, or null when it does not resolve.
     */
    public function ipv4(string $domain): ?string;
}
