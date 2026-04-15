<?php

namespace App\Services\Platform;

class CustomDomainService
{
    public function serverIp(): string
    {
        return config('services.forge.server_ip');
    }

    public function isValidFormat(string $domain): bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?(\.[a-zA-Z]{2,})+$/', $domain);
    }

    public function isDnsVerified(string $domain): bool
    {
        $ip = gethostbyname($domain);

        return $ip === $this->serverIp();
    }

    public function provisionSsl(string $domain): ?bool
    {
        if (! ForgeService::isConfigured()) {
            return null;
        }

        return resolve(ForgeService::class)->obtainSslCertificate($domain);
    }
}
