<?php

namespace App\Http\Middleware;

use App\Services\Settings\TenantSettings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! tenant()) {
            return $next($request);
        }

        if (! auth()->check()) {
            return $next($request);
        }

        if ($request->routeIs('filament.admin.auth.*')) {
            return $next($request);
        }

        if ($request->routeIs('filament.admin.pages.onboarding') || Str::contains($request->path(), 'onboarding')) {
            return $next($request);
        }

        if ($request->routeIs('livewire.*') || $request->is('livewire/*') || Str::startsWith($request->path(), 'livewire-')) {
            return $next($request);
        }

        try {
            $settings = resolve(TenantSettings::class);

            if ($settings->onboarding->completedAt === null) {
                return redirect()->to(url('/admin/onboarding'));
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to check onboarding status', [
                'tenant' => tenant()->getTenantKey(),
                'error' => $e->getMessage(),
            ]);
        }

        return $next($request);
    }
}
