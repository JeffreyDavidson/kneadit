<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        // Only check if tenancy is initialized
        if (! tenant()) {
            return $next($request);
        }

        // Skip for unauthenticated users (let them reach login page)
        if (! auth()->check()) {
            return $next($request);
        }

        // Skip login/logout/register routes
        if ($request->routeIs('filament.admin.auth.*')) {
            return $next($request);
        }

        // Skip if already on the onboarding page
        if ($request->routeIs('filament.admin.pages.onboarding') || Str::contains($request->path(), 'onboarding')) {
            return $next($request);
        }

        // Skip for livewire/filament update requests (hashed paths use livewire-* not livewire/*)
        if ($request->routeIs('livewire.*') || $request->is('livewire/*') || Str::startsWith($request->path(), 'livewire-')) {
            return $next($request);
        }

        // Check if onboarding is complete
        try {
            if (settings('onboarding_completed_at') === null) {
                return redirect()->to(url('/admin/onboarding'));
            }
        } catch (\Throwable $e) {
            return $next($request);
        }

        return $next($request);
    }
}
