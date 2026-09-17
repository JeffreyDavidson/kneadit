<?php

namespace App\Http\Controllers\Tenant\Orders;

use App\Http\Controllers\Controller;
use App\Models\Orders\Order;
use App\Services\Orders\OrderAccessGuard;
use App\Services\Stripe\StripeCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StripeSuccessController extends Controller
{
    /**
     * Stripe checkout success callback.
     */
    public function __invoke(Request $request, Order $order, StripeCheckoutService $stripeService): RedirectResponse
    {
        $sessionId = $request->string('session_id')->toString();

        if ($sessionId === '') {
            abort(403);
        }

        $completedOrder = $stripeService->handleCheckoutComplete($sessionId);

        if (! $completedOrder instanceof Order || $completedOrder->isNot($order)) {
            abort(403);
        }

        OrderAccessGuard::grant($order);

        return to_route('order.confirmation', $order)
            ->with('success', 'Payment successful! Your order has been placed.');
    }
}
