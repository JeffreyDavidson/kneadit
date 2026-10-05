<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cashier only verifies webhook signatures when a secret is configured, so
 * refuse to process events without one outside local development and tests.
 */
class RequireStripeWebhookSecret
{
    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $secret = Config::get('cashier.webhook.secret');

        if (is_string($secret) && $secret !== '') {
            return $next($request);
        }

        if (app()->environment(['local', 'testing'])) {
            return $next($request);
        }

        Log::error('STRIPE_WEBHOOK_SECRET not configured');

        return response('Webhook secret not configured', 500);
    }
}
