<?php

namespace App\Services\Platform;

use Stancl\Tenancy\Database\Models\Domain;

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

    public function addDomain(mixed $tenant, string $domain): void
    {
        $tenant->update(['custom_domain' => $domain]);

        if (! Domain::query()->where('domain', $domain)->exists()) {
            $tenant->domains()->create(['domain' => $domain]);
        }

        if (ForgeService::isConfigured()) {
            resolve(ForgeService::class)->addDomainAlias($domain);
        }
    }

    public function removeDomain(mixed $tenant): void
    {
        $oldDomain = $tenant->custom_domain;

        if ($oldDomain) {
            Domain::query()->where('domain', $oldDomain)->where('tenant_id', $tenant->id)->delete();

            if (ForgeService::isConfigured()) {
                resolve(ForgeService::class)->removeDomainAlias($oldDomain);
            }
        }

        $tenant->update(['custom_domain' => null]);
    }

    public function provisionSsl(string $domain): ?bool
    {
        if (! ForgeService::isConfigured()) {
            return null;
        }

        return resolve(ForgeService::class)->obtainSslCertificate($domain);
    }
}
