<?php

declare(strict_types=1);

namespace App\Tenancy\Middleware;

use Illuminate\Support\Facades\Config;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain as StanclInitializeTenancyBySubdomain;

/**
 * Stancl only recognises a subdomain when the host ends with a central domain. Production serves
 * bakeries at `{id}.{tenancy.tenant_domain}`, which is not a central domain, so this also treats
 * a single label in front of the tenant domain as a bakery subdomain.
 */
class InitializeTenancyBySubdomain extends StanclInitializeTenancyBySubdomain
{
    /** The bakery label when the host is exactly `{label}.{tenant domain}`, otherwise null. */
    public static function tenantDomainLabel(string $host): ?string
    {
        $tenantDomain = Config::get('tenancy.tenant_domain');

        if (! is_string($tenantDomain) || $tenantDomain === '') {
            return null;
        }

        $suffix = ".{$tenantDomain}";

        if (! str_ends_with($host, $suffix)) {
            return null;
        }

        $label = substr($host, 0, -strlen($suffix));

        return $label === '' || str_contains($label, '.') ? null : $label;
    }

    protected function makeSubdomain(string $hostname): mixed
    {
        return self::tenantDomainLabel($hostname) ?? parent::makeSubdomain($hostname);
    }
}
