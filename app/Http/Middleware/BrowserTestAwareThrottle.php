<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Symfony\Component\HttpFoundation\Response;

/**
 * Drop-in replacement for Laravel's `throttle` middleware that bypasses
 * rate limiting for the canonical browser-test tenant. Pest browser tests
 * fire enough requests in a short window to trip per-route limits like
 * `throttle:10,1`, which masks real test failures behind a 429 page.
 *
 * The bypass is host-scoped so it only ever affects local browser-test
 * traffic — production tenants always go through standard throttling.
 */
class BrowserTestAwareThrottle extends ThrottleRequests
{
    public function handle($request, Closure $next, $maxAttempts = 60, $decayMinutes = 1, $prefix = ''): Response
    {
        if ($request->getHost() === 'browser-test.kneadit.test') {
            return $next($request);
        }

        return parent::handle($request, $next, $maxAttempts, $decayMinutes, $prefix);
    }
}
