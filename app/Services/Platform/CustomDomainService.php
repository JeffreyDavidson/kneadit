<?php

namespace App\Services\Platform;

use App\Enums\Platform\DomainCheck;
use App\Services\Platform\Contracts\DnsResolver;
use App\Services\Platform\Contracts\HttpsProbe;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Support\Uri;

class CustomDomainService
{
    public function __construct(
        private readonly ForgeService $forge,
        private readonly DnsResolver $dns,
        private readonly HttpsProbe $https,
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
        return $this->dns->ipv4($domain) === $this->serverIp();
    }

    /**
     * Whether the domain answers over HTTPS with the ownership proof only this application
     * can produce for that exact host.
     */
    public function hasOwnershipProof(string $domain): bool
    {
        return $this->https->proves($domain);
    }

    /**
     * A domain is usable in links when it reaches this application over HTTPS: either its
     * A record is the server address and it answers with the ownership proof, or it
     * resolves elsewhere (a proxy such as Cloudflare) yet still answers with the proof.
     * A domain that does not answer with the proof is neither pointing here nor proxied here.
     */
    public function verify(string $domain): DomainCheck
    {
        if (! $this->isDnsVerified($domain)) {
            return $this->hasOwnershipProof($domain)
                ? DomainCheck::VerifiedThroughProxy
                : DomainCheck::DnsMissing;
        }

        if (! $this->hasOwnershipProof($domain)) {
            return DomainCheck::HttpsUnavailable;
        }

        return DomainCheck::Verified;
    }

    public function provisionSsl(string $domain): ?bool
    {
        if (! ForgeService::isConfigured()) {
            return null;
        }

        return $this->forge->obtainSslCertificate($domain);
    }
}
