<?php

namespace App\Http\Middleware;

use App\Enums\Platform\SubscriptionTier;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscribed
{
    public function handle(Request $request, Closure $next, ?string $plan = null): Response
    {
        if (! $request->user()?->subscribed('default')) {
            if ($request->user()?->onTrial()) {
                return $next($request);
            }

            return to_route('billing.plans');
        }

        $tier = $plan ? SubscriptionTier::tryFrom($plan) : null;

        abort_if($tier && ! Gate::forUser($request->user())->allows('has-plan', $tier), 403, 'Your current plan does not include this feature. Please upgrade.');

        return $next($request);
    }
}
