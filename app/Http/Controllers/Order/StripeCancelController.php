<?php

namespace App\Http\Controllers\Order;

use App\Actions\Stripe\CancelStripeCheckout;
use App\Http\Controllers\Controller;
use App\Models\Orders\Order;
use Illuminate\Http\RedirectResponse;

class StripeCancelController extends Controller
{
    public function __invoke(Order $order, CancelStripeCheckout $cancelCheckout): RedirectResponse
    {
        $cancelCheckout($order);

        return to_route('order.confirmation', $order)
            ->with('warning', 'Payment was not completed. You can pay later or contact the baker.');
    }
}
