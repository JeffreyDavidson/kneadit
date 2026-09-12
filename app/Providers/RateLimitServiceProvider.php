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

        $key = function (Request $request): string {
            $identifier = $request->user()?->getAuthIdentifier();

            if (is_string($identifier) || is_int($identifier)) {
                return (string) $identifier;
            }

            return $request->ip() ?? 'unknown';
        };

        $bypass = fn (Request $request): bool => $request->getHost() === 'browser-test.kneadit.test';

        RateLimiter::for('sensitive-write', fn (Request $request) => $bypass($request)
            ? Limit::none()
            : Limit::perMinute(5)->by($key($request)));

        RateLimiter::for('verification-resend', fn (Request $request) => $bypass($request)
            ? Limit::none()
            : Limit::perMinute(6)->by($key($request)));

        RateLimiter::for('form-write', fn (Request $request) => $bypass($request)
            ? Limit::none()
            : Limit::perMinute(10)->by($key($request)));

        RateLimiter::for('referral-claim', fn (Request $request) => $bypass($request)
            ? Limit::none()
            : Limit::perMinute(30)->by($key($request)));

        RateLimiter::for('frequent-poll', fn (Request $request) => $bypass($request)
            ? Limit::none()
            : Limit::perMinute(60)->by($key($request)));
    }
}
