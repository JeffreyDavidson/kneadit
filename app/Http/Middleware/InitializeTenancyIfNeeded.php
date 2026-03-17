<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedOnDomainException;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancyIfNeeded
{
    public function handle(Request $request, Closure $next): Response
    {
        // Skip if tenancy is already initialized
        if (tenant()) {
            return $next($request);
        }

        // Redirect www to apex domain
        $host = $request->getHost();
        if ($host === 'www.getkneadit.app') {
            return redirect()->to(
                $request->getScheme().'://getkneadit.app'.$request->getRequestUri(),
                301
            );
        }

        // Skip if we're on a central domain
        if (in_array($host, config('tenancy.central_domains', []), true)) {
            return $next($request);
        }

        // We're on a subdomain — initialize tenancy
        try {
            return resolve(InitializeTenancyByDomainOrSubdomain::class)->handle($request, $next);
        } catch (TenantCouldNotBeIdentifiedOnDomainException $e) {
            abort(404, 'Bakery not found.');
        }
    }
}
