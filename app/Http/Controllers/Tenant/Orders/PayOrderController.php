<?php

namespace App\Http\Controllers\Tenant\Orders;

use App\Http\Controllers\Controller;
use App\Models\Orders\Order;
use App\Services\Stripe\StripeCheckoutService;
use Illuminate\Http\RedirectResponse;

/**
 * "Pay now" on the order page for a customer who left Stripe without paying.
 */
class PayOrderController extends Controller
{
    public function __invoke(Order $order, StripeCheckoutService $stripeService): RedirectResponse
    {
        if (! $stripeService->canPayOnline($order)) {
            return to_route('order.confirmation', $order);
        }

        $checkoutUrl = $stripeService->resumeOrCreateCheckout($order);

        if ($checkoutUrl === null) {
            return to_route('order.confirmation', $order)
                ->with('warning', 'Online payment is not available right now. Please try again shortly or contact the baker.');
        }

        return redirect($checkoutUrl);
    }
}
