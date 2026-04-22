<?php

namespace App\Http\Controllers\Order;

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
        $sessionId = $request->query('session_id');

        if ($sessionId) {
            $stripeService->handleCheckoutComplete($sessionId);
        }

        // Returning from Stripe checkout for THIS order is sufficient
        // proof of ownership for this session.
        OrderAccessGuard::grant($order);

        return to_route('order.confirmation', $order)
            ->with('success', 'Payment successful! Your order has been placed.');
    }
}
