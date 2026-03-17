<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Category;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\StripeCheckoutService;

class OrderController extends Controller
{
    public function index()
    {
        $categories = Category::with(['products' => function ($q) {
            $q->where('is_active', true)->orderBy('name');
        }, 'products.seasonalItems'])->orderBy('sort_order')->get();

        // Filter out products that are seasonal but not currently in season
        $categories->each(function ($category) {
            $category->setRelation(
                'products',
                $category->products->filter(fn ($product) => $product->isInSeason())
            );
        });

        return view('order', compact('categories'));
    }

    /**
     * Submit an order, delegating business logic to OrderService.
     */
    public function store(StoreOrderRequest $request, OrderService $orderService)
    {
        $validated = $request->validated();

        $order = $orderService->createOrder(
            $validated,
            $request->input('coupon_id'),
            $request->input('gift_card_id'),
        );

        if (! $order) {
            return back()->withErrors(['delivery_date' => 'Sorry, this date is fully booked. Please choose another date.']);
        }

        // If baker has Stripe Connect enabled and order total > 0, redirect to Stripe Checkout
        if ($order->total > 0 && StripeCheckoutService::isEnabled()) {
            $stripeService = new StripeCheckoutService;
            $session = $stripeService->createCheckoutSession(
                $order,
                route('order.stripe.success', $order).'?session_id={CHECKOUT_SESSION_ID}',
                route('order.stripe.cancel', $order),
            );

            if ($session) {
                return redirect($session->url);
            }

            // Stripe session creation failed — fall through to normal confirmation
        }

        return to_route('order.confirmation', $order)
            ->with('success', 'Order submitted successfully!');
    }

    /**
     * Show the order confirmation page.
     */
    public function show(Order $order)
    {
        $order->load('orderItems');

        return view('order-confirmation', compact('order'));
    }
}
