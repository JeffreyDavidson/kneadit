<?php

namespace App\Http\Middleware;

use App\Models\Staff\User;
use App\Services\Audit\ActorContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Populates ActorContext from the authenticated user on every web
 * request. The observer reads from ActorContext (and so do any jobs
 * dispatched mid-request) rather than reaching into auth() directly.
 */
class SetActorContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        ActorContext::set($user instanceof User ? $user : null);

        return $next($request);
    }
}
