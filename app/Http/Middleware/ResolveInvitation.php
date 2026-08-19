<?php

namespace App\Http\Middleware;

use App\Models\Staff\StaffInvitation;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveInvitation
{
    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $invitation = StaffInvitation::query()
            ->pending()
            ->where('token', $request->route('token'))
            ->firstOrFail();

        if ($invitation->expires_at->isPast()) {
            return response()->view('invitations.expired');
        }

        $request->attributes->set('invitation', $invitation);

        return $next($request);
    }
}
