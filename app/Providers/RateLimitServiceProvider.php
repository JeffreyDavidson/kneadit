<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RateLimitServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('webhooks', fn () => Limit::perMinute(30));

        // Named throttle limiters used by route definitions. Naming the
        // intent (sensitive-write, form-write, etc.) instead of repeating
        // raw `throttle:5,1` / `throttle:10,1` everywhere makes route
        // files readable and lets the browser-test bypass live in one
        // place rather than as a dedicated middleware subclass.
        $key = function (Request $request): string {
            $identifier = $request->user()?->getAuthIdentifier();

            if (is_string($identifier) || is_int($identifier)) {
                return (string) $identifier;
            }

            return $request->ip() ?? 'unknown';
        };

        $bypass = fn (Request $request): bool => $request->getHost() === 'browser-test.kneadit.test';

        // Auth + payment + order-modify + invite + central marketing contact.
        RateLimiter::for('sensitive-write', fn (Request $request) => $bypass($request)
            ? Limit::none()
            : Limit::perMinute(5)->by($key($request)));

        // Verification email resend — intentionally slow; spamming the
        // inbox is the abuse pattern this guards against.
        RateLimiter::for('verification-resend', fn (Request $request) => $bypass($request)
            ? Limit::none()
            : Limit::perMinute(6)->by($key($request)));

        // Storefront forms + order writes + API writes — the catch-all
        // for "user just submitted a form, allow a few retries on hiccups."
        RateLimiter::for('form-write', fn (Request $request) => $bypass($request)
            ? Limit::none()
            : Limit::perMinute(10)->by($key($request)));

        // Referral code claim — bumped above form-write because users
        // legitimately retry with multiple variations and the abuse cost
        // is low (claim is idempotent against a known set of codes).
        RateLimiter::for('referral-claim', fn (Request $request) => $bypass($request)
            ? Limit::none()
            : Limit::perMinute(30)->by($key($request)));

        // High-frequency reads + light passive writes (cart persist,
        // pickup-slots lookup, CSP-report intake, public API reads).
        RateLimiter::for('frequent-poll', fn (Request $request) => $bypass($request)
            ? Limit::none()
            : Limit::perMinute(60)->by($key($request)));
    }
}
