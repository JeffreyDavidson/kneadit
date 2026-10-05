<?php

declare(strict_types=1);

namespace App\Tenancy\Middleware;

use Closure;
use Stancl\Tenancy\Database\Models\Domain;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain as StanclInitializeTenancyByDomainOrSubdomain;

/**
 * Identifies a bakery by its subdomain (a central domain suffix or `{id}.{tenancy.tenant_domain}`)
 * or, for any other host, by its custom domain.
 */
class InitializeTenancyByDomainOrSubdomain extends StanclInitializeTenancyByDomainOrSubdomain
{
    public function handle($request, Closure $next): mixed
    {
        if ($this->isSubdomain($request->getHost())) {
            return resolve(InitializeTenancyBySubdomain::class)->handle($request, $next);
        }

        return resolve(InitializeTenancyByDomain::class)->handle($request, $next);
    }

    protected function isSubdomain(string $hostname): bool
    {
        if (parent::isSubdomain($hostname)) {
            return true;
        }

        return InitializeTenancyBySubdomain::tenantDomainLabel($hostname) !== null
            && ! Domain::query()->where('domain', $hostname)->exists();
    }
}
