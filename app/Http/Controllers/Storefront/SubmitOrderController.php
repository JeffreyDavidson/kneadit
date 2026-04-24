<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\Orders\CreateOrder;
use App\Exceptions\Orders\InsufficientStockException;
use App\Exceptions\Orders\MinimumOrderAmountNotMetException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StoreOrderRequest;
use App\Services\Orders\OrderAccessGuard;
use App\Services\Stripe\StripeCheckoutService;
use Illuminate\Http\RedirectResponse;

class SubmitOrderController extends Controller
{
    public function __invoke(StoreOrderRequest $request, CreateOrder $createOrder, StripeCheckoutService $stripeService): RedirectResponse
    {
        $content = settingsPageContent('order');

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
        } catch (InsufficientStockException $e) {
            return back()
                ->withInput()
                ->withErrors(['items' => sprintf(
                    'Sorry, we don\'t have enough %s in stock right now. Please reduce the quantity or remove an item.',
                    implode(', ', $e->shortages),
                )]);
        }

        if (! $order) {
            return back()->withErrors([
                'delivery_date' => $content['flash_full'] ?? 'Sorry, this date is fully booked. Please choose another date.',
            ]);
        }

        // Grant the customer's current session access to view this order
        // without re-verifying their email (they just placed it).
        OrderAccessGuard::grant($order);

        $checkoutUrl = $stripeService->redirectToCheckout($order);
        if ($checkoutUrl) {
            return redirect($checkoutUrl);
        }

        return to_route('order.confirmation', $order)
            ->with('success', $content['flash_success'] ?? 'Order submitted successfully!');
    }
}
