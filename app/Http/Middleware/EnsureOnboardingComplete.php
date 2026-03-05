<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        // Only check if tenancy is initialized
        if (! tenant()) {
            return $next($request);
        }

        // Skip if already on the onboarding page
        if ($request->routeIs('filament.admin.pages.onboarding') || str_contains($request->path(), 'onboarding')) {
            return $next($request);
        }

        // Skip for livewire/filament update requests
        if ($request->routeIs('livewire.*') || $request->is('livewire/*')) {
            return $next($request);
        }

        // Check if onboarding is complete
        if (Setting::get('onboarding_completed_at') === null) {
            return redirect()->to(url('/admin/onboarding'));
        }

        return $next($request);
    }
}
