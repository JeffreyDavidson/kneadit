<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\Orders\CreateOrder;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StoreOrderRequest;
use App\Services\Stripe\StripeCheckoutService;
use Illuminate\Http\RedirectResponse;

class SubmitOrderController extends Controller
{
    public function __invoke(StoreOrderRequest $request, CreateOrder $createOrder, StripeCheckoutService $stripeService): RedirectResponse
    {
        $content = settingsPageContent('order');
        $order = $createOrder($request->toData());

        if (! $order) {
            return back()->withErrors([
                'delivery_date' => $content['flash_full'] ?? 'Sorry, this date is fully booked. Please choose another date.',
            ]);
        }

        $checkoutUrl = $stripeService->redirectToCheckout($order);
        if ($checkoutUrl) {
            return redirect($checkoutUrl);
        }

        return to_route('order.confirmation', $order)
            ->with('success', $content['flash_success'] ?? 'Order submitted successfully!');
    }
}
