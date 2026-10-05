<?php

declare(strict_types=1);

namespace App\Tenancy\Middleware;

use App\Models\Platform\Tenant;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
            return resolve(InitializeTenancyBySubdomain::class)->handle(
                $request,
                fn (Request $request): mixed => $this->redirectOldSubdomain($request) ?? $next($request),
            );
        }

        return resolve(InitializeTenancyByDomain::class)->handle($request, $next);
    }

    /**
     * A bakery keeps its old subdomain's `domains` row after changing subdomain, so old links
     * still resolve; GET and HEAD requests on it are sent permanently to the current one.
     * Other methods proceed so an in-flight form post is not lost. Custom domains never get here.
     */
    private function redirectOldSubdomain(Request $request): ?RedirectResponse
    {
        $tenant = tenant();

        if (! $tenant instanceof Tenant || blank($tenant->subdomain) || ! $request->isMethodSafe()) {
            return null;
        }

        $host = $request->getHost();
        $label = Str::before($host, '.');

        if ($label === $tenant->subdomain) {
            return null;
        }

        $newHost = "{$tenant->subdomain}.".Str::after($host, '.');
        $port = $request->getPort();
        $authority = in_array($port, [80, 443], true) ? $newHost : "{$newHost}:{$port}";

        return redirect()->to("{$request->getScheme()}://{$authority}{$request->getRequestUri()}", 301);
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
