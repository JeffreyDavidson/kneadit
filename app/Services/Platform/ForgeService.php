<?php

namespace App\Services\Platform;

use App\Services\Platform\Contracts\ForgeClient;

class ForgeService
{
    public function __construct(private readonly ForgeClient $client) {}

    public static function isConfigured(): bool
    {
        return ! empty(config('services.forge.token'))
            && ! empty(config('services.forge.organization'))
            && ! empty(config('services.forge.server_id'))
            && ! empty(config('services.forge.site_id'));
    }

    public function addDomainAlias(string $domain): bool
    {
        return $this->client->addDomainAlias($domain);
    }

    public function obtainSslCertificate(string $domain): bool
    {
        return $this->client->obtainSslCertificate($domain);
    }

    public function removeDomainAlias(string $domain): bool
    {
        return $this->client->removeDomainAlias($domain);
    }
}
