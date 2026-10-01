<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Require a verified email for customer-account routes.
 *
 * Requests without a logged-in customer pass through, so the route's own
 * authentication (auth:customer, or the controller) decides what happens
 * to them. An unverified customer gets 403 for JSON requests and is
 * redirected to the verification notice otherwise.
 */
class EnsureCustomerEmailIsVerified
{
    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $customer = $request->user('customer');

        if (! $customer instanceof MustVerifyEmail || $customer->hasVerifiedEmail()) {
            return $next($request);
        }

        abort_if($request->expectsJson(), 403, 'You must verify your email address to use your account.');

        return to_route('account.email.verify.notice');
    }
}
