<?php

namespace App\Http\Middleware;

use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use App\Services\Audit\ActorContext;
use Closure;
use Illuminate\Http\Request;
use Sentry\State\Scope;
use Symfony\Component\HttpFoundation\Response;

use function Sentry\configureScope;

/**
 * Populates ActorContext from the authenticated user on every web
 * request. The observer reads from ActorContext (and so do any jobs
 * dispatched mid-request) rather than reaching into auth() directly.
 *
 * Also tags the active Sentry scope with the user + tenant so any
 * exception captured during the request is attributable. No-op when
 * Sentry isn't bound (local/test without SENTRY_LARAVEL_DSN).
 */
class SetActorContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        ActorContext::set($user instanceof User ? $user : null);

        if (app()->bound('sentry')) {
            configureScope(function (Scope $scope) use ($user): void {
                if ($user instanceof User) {
                    $scope->setUser([
                        'id' => (string) $user->id,
                        'email' => $user->email,
                    ]);
                }

                $tenant = tenancy()->tenant;

                if ($tenant instanceof Tenant) {
                    $scope->setTag('tenant_id', (string) $tenant->getTenantKey());
                }
            });
        }

        return $next($request);
    }
}
