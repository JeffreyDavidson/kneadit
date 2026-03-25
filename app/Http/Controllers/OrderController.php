<?php

namespace App\Http\Controllers;

use App\Actions\Orders\CreateOrder;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Services\StripeCheckoutService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    public function index(): View
    {
        $categories = Category::with(['products' => function (HasMany $q) {
            $q->where('is_active', true)->orderBy('name');
        }, 'products.seasonalItems'])->orderBy('sort_order')->get();

        // Filter out products that are seasonal but not currently in season
        $categories->each(function (Category $category) {
            $category->setRelation(
                'products',
                $category->products->filter(fn (Product $product) => $product->isInSeason())
            );
        });

        return view('order', compact('categories'));
    }

    /**
     * Submit an order, delegating business logic to CreateOrder action.
     */
    public function store(StoreOrderRequest $request, CreateOrder $createOrder): RedirectResponse
    {
        $validated = $request->validated();

        $order = $createOrder(
            $validated,
            $validated['coupon_id'] ?? null,
            $validated['gift_card_id'] ?? null,
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
    public function show(Order $order): View
    {
        $order->load('orderItems');

        return view('order-confirmation', compact('order'));
    }
}
