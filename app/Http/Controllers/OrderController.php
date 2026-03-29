<?php

namespace App\Http\Controllers;

use App\Actions\Orders\CreateOrder;
use App\DataTransferObjects\CreateOrderData;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Category;
use App\Models\Order;
use App\Services\Stripe\StripeCheckoutService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    public function index(): View
    {
        $categories = Category::with(['products' => fn (HasMany $q) => $q->where('is_active', true)->orderBy('name')])->orderBy('sort_order')->get();

        return view('order', compact('categories'));
    }

    /**
     * Submit an order, delegating business logic to CreateOrder action.
     */
    public function store(StoreOrderRequest $request, CreateOrder $createOrder, StripeCheckoutService $stripeService): RedirectResponse
    {
        $validated = $request->validated();

        $order = $createOrder(CreateOrderData::fromArray($validated));

        if (! $order) {
            return back()->withErrors(['delivery_date' => 'Sorry, this date is fully booked. Please choose another date.']);
        }

        // If baker has Stripe Connect enabled and order total > 0, redirect to Stripe Checkout
        if ($order->total > 0 && StripeCheckoutService::isEnabled()) {

            $session = $stripeService->createCheckoutSession(
                $order,
                route('order.stripe.success', $order) . '?session_id={CHECKOUT_SESSION_ID}',
                route('order.stripe.cancel', $order),
            );

            if ($session) {
                return redirect((string) $session->url);
            }

            // Stripe session creation failed — fall through to normal confirmation
        }

        return to_route('order.confirmation', $order)
            ->with('success', 'Order submitted successfully!');
    }

    /**
     * Show the order confirmation page.
     */
    public function show(Order $order): View
    {
        $order->load('orderItems');

        return view('order-confirmation', compact('order'));
    }
}
