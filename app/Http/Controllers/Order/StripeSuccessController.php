<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\StripeCheckoutService;
use Illuminate\Http\Request;

class StripeSuccessController extends Controller
{
    /**
     * Stripe checkout success callback.
     */
    public function __invoke(Request $request, Order $order)
    {
        $sessionId = $request->query('session_id');

        if ($sessionId) {
            $stripeService = new StripeCheckoutService;
            $stripeService->handleCheckoutComplete($sessionId);
        }

        return to_route('order.confirmation', $order)
            ->with('success', 'Payment successful! Your order has been placed.');
    }
}
