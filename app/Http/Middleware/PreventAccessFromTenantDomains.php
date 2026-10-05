<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps central-only routes (subscription billing) off bakery hosts. The web
 * group has already put the request's bakery in context there, and these
 * routes read the central database, so answering would fail with a 500.
 */
class PreventAccessFromTenantDomains
{
    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(in_array($request->getHost(), Config::array('tenancy.central_domains'), true), 404);

        return $next($request);
    }
}
