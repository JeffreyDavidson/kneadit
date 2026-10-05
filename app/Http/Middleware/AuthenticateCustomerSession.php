<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sign a customer out of every session that predates their last password change.
 *
 * Laravel's AuthenticateSession only watches the default (staff) guard, so this
 * does the same for the `customer` guard. The password hash current at sign-in is
 * remembered in the session (see the `Login` listener in AuthServiceProvider); when the
 * customer's hash no longer matches, the session is discarded and the request
 * carries on as a guest, so the route's own auth middleware decides what happens.
 * A session that has no hash yet (it predates this check) adopts the current one.
 */
class AuthenticateCustomerSession
{
    public const string SESSION_KEY = 'password_hash_customer';

    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $guard = auth()->guard('customer');
        $customer = $guard instanceof SessionGuard ? $guard->user() : null;

        if ($customer === null || ! $request->hasSession() || blank($customer->getAuthPassword())) {
            return $next($request);
        }

        $stored = $request->session()->get(self::SESSION_KEY);
        $current = self::passwordHash($customer);

        if (is_string($stored) && ! hash_equals($stored, $current)) {
            $guard->logoutCurrentDevice();
            $request->session()->invalidate();

            return $next($request);
        }

        $request->session()->put(self::SESSION_KEY, $current);

        return $next($request);
    }

    public static function passwordHash(Authenticatable $customer): string
    {
        return hash_hmac('sha256', $customer->getAuthPassword(), config()->string('app.key'));
    }
}
