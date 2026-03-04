<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscribed
{
    public function handle(Request $request, Closure $next, ?string $plan = null): Response
    {
        if (! $request->user()?->subscribed('default')) {
            if ($request->user()?->onTrial()) {
                return $next($request);
            }

            return redirect()->route('billing.plans');
        }

        if ($plan && ! $this->hasRequiredPlan($request->user(), $plan)) {
            abort(403, 'Your current plan does not include this feature. Please upgrade.');
        }

        return $next($request);
    }

    private function hasRequiredPlan($user, string $requiredPlan): bool
    {
        $hierarchy = ['starter' => 1, 'growth' => 2, 'pro' => 3];
        $currentPlan = $user->currentPlan();
        $currentLevel = $hierarchy[$currentPlan] ?? 0;
        $requiredLevel = $hierarchy[$requiredPlan] ?? 0;

        return $currentLevel >= $requiredLevel;
    }
}
