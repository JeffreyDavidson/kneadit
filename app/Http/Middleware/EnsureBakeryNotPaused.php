<?php

namespace App\Http\Middleware;

use App\Models\Platform\Tenant;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Refuses tenant API requests while the bakery is paused. Unlike
 * EnsureStorefrontEnabled this never looks at storefront_enabled, so a bakery
 * that uses its own website keeps its API until it is actually paused.
 */
class EnsureBakeryNotPaused
{
    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenancy()->tenant;

        if ($tenant instanceof Tenant && $tenant->is_paused) {
            return new JsonResponse(
                ['message' => 'This bakery is not currently accepting orders.'],
                Response::HTTP_FORBIDDEN,
            );
        }

        return $next($request);
    }
}
