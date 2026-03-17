<?php

namespace App\Http\Middleware;

use App\Enums\SubscriptionTier;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanAccess
{
    public function handle(Request $request, Closure $next, string $requiredPlan = 'starter'): Response
    {
        $current = SubscriptionTier::tryFrom(tenant()?->plan ?? 'starter');
        $required = SubscriptionTier::tryFrom($requiredPlan);

        if (! $current || ! $required || ! $current->meetsRequirement($required)) {
            return to_route('filament.admin.pages.upgrade-plan');
        }

        return $next($request);
    }
}
