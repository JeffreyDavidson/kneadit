<?php

namespace App\Http\Middleware;

use App\Enums\Platform\SubscriptionTier;
use App\Models\Staff\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscribed
{
    public function handle(Request $request, Closure $next, ?string $plan = null): Response
    {
        $user = $request->user();

        // Platform-admin-granted comp accounts bypass billing entirely. The
        // per-plan feature gate below (has-plan) still runs and will pass
        // because SubscriptionTier::resolve() returns Pro for them.
        if ($user instanceof User && $user->tenants()->where('free_forever', true)->exists()) {
            return $next($request);
        }

        if (! $user instanceof User || ! $user->subscribed('default')) {
            if ($user instanceof User && $user->onTrial()) {
                return $next($request);
            }

            return to_route('billing.plans');
        }

        $tier = $plan ? SubscriptionTier::tryFrom($plan) : null;

        abort_if($tier && ! Gate::forUser($user)->allows('has-plan', $tier), 403, 'Your current plan does not include this feature. Please upgrade.');

        return $next($request);
    }
}
