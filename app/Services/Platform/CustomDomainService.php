<?php

namespace App\Services\Platform;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Support\Uri;

class CustomDomainService
{
    public function __construct(
        private readonly ForgeService $forge,
    ) {}

    public function serverIp(): string
    {
        return Config::string('services.forge.server_ip');
    }

    /**
     * Reduces what a bakery typed (a URL, mixed case, a trailing dot) to a bare lowercase hostname.
     */
    public function normalize(string $domain): string
    {
        return Str::of($domain)
            ->trim()
            ->lower()
            ->replaceMatches('#^[a-z][a-z0-9+.-]*://#', '')
            ->replaceMatches('#[/?\#].*$#', '')
            ->rtrim('.')
            ->toString();
    }

    /**
     * Whether the host is the platform's own: a central domain, or the tenant domain
     * and any subdomain of it (every bakery's storefront lives at `{id}.{tenant domain}`).
     */
    public function isPlatformDomain(string $domain): bool
    {
        if (in_array($domain, Config::array('tenancy.central_domains'), true)) {
            return true;
        }

        $tenantDomain = Config::get('tenancy.tenant_domain');

        if (! is_string($tenantDomain) || $tenantDomain === '') {
            $tenantDomain = (string) Uri::of(Config::string('app.url'))->host();
        }

        return $tenantDomain !== '' && ($domain === $tenantDomain || str_ends_with($domain, ".{$tenantDomain}"));
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

        return $this->forge->obtainSslCertificate($domain);
    }
}
