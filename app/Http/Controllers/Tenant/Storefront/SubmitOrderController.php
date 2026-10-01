<?php

namespace App\Http\Controllers\Tenant\Storefront;

use App\Actions\Orders\CreateOrder;
use App\Exceptions\Orders\InsufficientStockException;
use App\Exceptions\Orders\MinimumOrderAmountNotMetException;
use App\Exceptions\Orders\PickupSlotUnavailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StoreOrderRequest;
use App\Models\Orders\Order;
use App\Services\Orders\OrderAccessGuard;
use App\Services\Stripe\StripeCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class SubmitOrderController extends Controller
{
    public function __invoke(StoreOrderRequest $request, CreateOrder $createOrder, StripeCheckoutService $stripeService): RedirectResponse
    {
        $content = settingsPageContent('order');

        // Domain failures are thrown as validation errors so the handler answers
        // the fetch-based form with a 422 and a plain form post with a redirect back.
        try {
            $order = $createOrder($request->toData());
        } catch (MinimumOrderAmountNotMetException $e) {
            throw ValidationException::withMessages(['items' => sprintf(
                'Minimum %s order is $%.2f. Please add more items to continue.',
                $e->deliveryType,
                $e->minimum,
            )]);
        } catch (InsufficientStockException $e) {
            throw ValidationException::withMessages(['items' => sprintf(
                'Sorry, we don\'t have enough %s in stock right now. Please reduce the quantity or remove an item.',
                implode(', ', $e->shortages),
            )]);
        } catch (PickupSlotUnavailableException) {
            throw ValidationException::withMessages([
                'delivery_time' => PickupSlotUnavailableException::CUSTOMER_MESSAGE,
            ]);
        }

        if (! $order instanceof Order) {
            throw ValidationException::withMessages([
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
