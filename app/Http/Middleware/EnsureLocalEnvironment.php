<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Hides developer-only pages (such as the email previews) everywhere except a
 * developer's own machine: in any other environment they answer 404.
 */
class EnsureLocalEnvironment
{
    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(app()->isLocal(), 404);

        return $next($request);
    }
}
