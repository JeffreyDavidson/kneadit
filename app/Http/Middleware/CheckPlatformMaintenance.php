<?php

namespace App\Http\Middleware;

use App\Models\PlatformSetting;
use Closure;
use Illuminate\Http\Request;
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
        if (PlatformSetting::get('maintenance_mode') !== '1') {
            return $next($request);
        }

        $affectedServices = json_decode(PlatformSetting::get('affected_services', '[]'), true) ?: [];

        $currentService = $this->detectService($request);

        if (! in_array($currentService, $affectedServices)) {
            return $next($request);
        }

        $message = PlatformSetting::get('maintenance_message', 'We are currently performing scheduled maintenance.');
        $scheduledEnd = PlatformSetting::get('maintenance_scheduled_end');

        return response()
            ->view('maintenance', [
                'message' => $message,
                'scheduled_end' => $scheduledEnd,
            ], 503);
    }

    protected function detectService(Request $request): string
    {
        $path = $request->path();

        if (str_starts_with($path, 'api/') || $request->is('api/*')) {
            return 'api';
        }

        if (str_starts_with($path, 'admin') || $request->is('admin/*')) {
            return 'admin';
        }

        return 'storefront';
    }
}
