<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanAccess
{
    private static array $hierarchy = ['starter' => 1, 'growth' => 2, 'pro' => 3];

    public function handle(Request $request, Closure $next, string $requiredPlan = 'starter'): Response
    {
        $currentPlan = tenant()?->plan ?? 'starter';

        if ((self::$hierarchy[$currentPlan] ?? 1) < (self::$hierarchy[$requiredPlan] ?? 1)) {
            return redirect()->route('filament.admin.pages.upgrade-plan');
        }

        return $next($request);
    }
}
