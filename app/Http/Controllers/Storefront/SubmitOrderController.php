<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\Orders\CreateOrder;
use App\Exceptions\Orders\MinimumOrderAmountNotMetException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StoreOrderRequest;
use App\Services\Stripe\StripeCheckoutService;
use Illuminate\Http\RedirectResponse;

class SubmitOrderController extends Controller
{
    public function __invoke(StoreOrderRequest $request, CreateOrder $createOrder, StripeCheckoutService $stripeService): RedirectResponse
    {
        try {
            $order = $createOrder($request->toData());
        } catch (MinimumOrderAmountNotMetException $e) {
            return back()
                ->withInput()
                ->withErrors(['items' => sprintf(
                    'Minimum %s order is $%.2f. Please add more items to continue.',
                    $e->deliveryType,
                    $e->minimum,
                )]);
        }

        if (! $order) {
            return back()->withErrors(['delivery_date' => 'Sorry, this date is fully booked. Please choose another date.']);
        }

        $checkoutUrl = $stripeService->redirectToCheckout($order);
        if ($checkoutUrl) {
            return redirect($checkoutUrl);
        }

        return to_route('order.confirmation', $order)
            ->with('success', 'Order submitted successfully!');
    }
}
