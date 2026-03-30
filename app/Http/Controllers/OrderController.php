<?php

namespace App\Http\Controllers;

use App\Actions\Orders\CreateOrder;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Category;
use App\Models\Order;
use App\Services\Stripe\StripeCheckoutService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function index(): View
    {
        $categories = Category::with(['products' => fn (HasMany $q) => $q->where('is_active', true)->orderBy('name')])->orderBy('sort_order')->get();

        $leadTimeHours = settings('order_lead_time_hours', '24');
        $leadTimeDays = ceil($leadTimeHours / 24);
        $deliveryEnabled = settings('delivery_enabled', '1') === '1';
        $deliveryTiers = json_decode(settings('delivery_fee_tiers', '[]'), true);
        $allergyDisclaimer = settings('allergy_disclaimer');
        $paymentMethods = json_decode(settings('payment_methods_accepted', '[]'), true);
        $freeDeliveryMin = settings('free_delivery_minimum', '50');

        return view('order', [
            'categories' => $categories,
            'leadTimeHours' => $leadTimeHours,
            'leadTimeDays' => $leadTimeDays,
            'deliveryEnabled' => $deliveryEnabled,
            'deliveryTiers' => $deliveryTiers,
            'allergyDisclaimer' => $allergyDisclaimer,
            'paymentMethods' => $paymentMethods,
            'freeDeliveryMin' => $freeDeliveryMin,
        ]);
    }

    /**
     * Submit an order, delegating business logic to CreateOrder action.
     */
    public function store(StoreOrderRequest $request, CreateOrder $createOrder, StripeCheckoutService $stripeService): RedirectResponse
    {
        $validated = $request->validated();

        $order = $createOrder($request->toData());

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

        $heroImage = settings('hero_image');
        $heroImageUrl = $heroImage ? Storage::url($heroImage) : 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1920&q=80';
        $content = settingsPageContent('order_confirmation');
        $journeySteps = $content['journey_steps'] ?? [
            ['title' => 'Confirmation', 'description' => 'You\'ll receive an email confirmation with your order details shortly.'],
            ['title' => 'Preparation', 'description' => 'Our bakers will craft your items fresh on your scheduled date.'],
            [
                'title' => 'Delivery',
                'description_delivery' => 'We\'ll deliver your fresh items right to your door.',
                'description_pickup' => 'Your items will be warm and ready for you to pick up.',
            ],
        ];

        return view('order-confirmation', [
            'order' => $order,
            'heroImageUrl' => $heroImageUrl,
            'content' => $content,
            'journeySteps' => $journeySteps,
        ]);
    }
}
