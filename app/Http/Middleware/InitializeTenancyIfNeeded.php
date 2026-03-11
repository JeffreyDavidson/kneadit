<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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

        // Skip if we're on a central domain
        $host = $request->getHost();
        if (in_array($host, config('tenancy.central_domains', []), true)) {
            return $next($request);
        }

        // We're on a subdomain — initialize tenancy
        return app(InitializeTenancyByDomainOrSubdomain::class)->handle($request, $next);
    }
}
