<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Checks if the platform is in maintenance mode and returns a 503 response
 * for affected services.
 *
 * NOTE: This middleware needs to be registered in bootstrap/app.php:
 *   ->withMiddleware(function (Middleware $middleware) {
 *       $middleware->append(\App\Http\Middleware\CheckPlatformMaintenance::class);
 *   })
 */
class CheckPlatformMaintenance
{
    public function handle(Request $request, Closure $next): Response
    {
        if (platformSettings('maintenance_mode') !== '1') {
            return $next($request);
        }

        $affectedServices = json_decode(platformSettings('affected_services', '[]'), true) ?: [];

        $currentService = $this->detectService($request);

        if (! in_array($currentService, $affectedServices)) {
            return $next($request);
        }

        $message = platformSettings('maintenance_message', 'We are currently performing scheduled maintenance.');
        $scheduledEnd = platformSettings('maintenance_scheduled_end');

        return response()
            ->view('maintenance', [
                'message' => $message,
                'scheduled_end' => $scheduledEnd,
            ], 503);
    }

    protected function detectService(Request $request): string
    {
        $path = $request->path();

        if (Str::startsWith($path, 'api/') || $request->is('api/*')) {
            return 'api';
        }

        if (Str::startsWith($path, 'admin') || $request->is('admin/*')) {
            return 'admin';
        }

        return 'storefront';
    }
}
