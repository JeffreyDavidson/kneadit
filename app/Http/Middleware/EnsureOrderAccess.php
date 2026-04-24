<?php

namespace App\Http\Middleware;

use App\Models\Orders\Order;
use App\Services\Orders\OrderAccessGuard;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate order-by-number routes (confirmation, tracking, messages,
 * reorder, review) on session-verified ownership of the order.
 *
 * Route-model binding has already resolved the Order on the route
 * parameter (named "order"). If the session can't access it, JSON
 * requests get 403; HTML requests get redirected to the email
 * verification form with the original URL stashed for post-verify
 * redirect.
 */
class EnsureOrderAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $order = $request->route('order');

        abort_unless($order instanceof Order, 404);

        if (OrderAccessGuard::canAccess($order)) {
            return $next($request);
        }

        abort_if($request->expectsJson(), 403, 'You must verify your email to access this order.');

        return redirect()
            ->guest(route('order.verify.show', ['order' => $order->order_number]));
    }
}
