<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\StripeClient;

class StripeCheckoutService
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('cashier.secret'));
    }

    /**
     * Check if the current tenant has Stripe Connect enabled and ready.
     */
    public static function isEnabled(): bool
    {
        $methods = Setting::get('payment_methods');
        $methods = $methods ? json_decode($methods, true) : [];

        if (! in_array('stripe', $methods)) {
            return false;
        }

        $connectId = Setting::get('stripe_connect_id');
        $chargesEnabled = Setting::get('stripe_connect_charges_enabled', '0');

        return $connectId && $chargesEnabled === '1';
    }

    /**
     * Get the connected account ID for the current tenant.
     */
    public static function getConnectId(): ?string
    {
        return Setting::get('stripe_connect_id');
    }

    /**
     * Create a Stripe Checkout Session on the baker's connected account.
     */
    public function createCheckoutSession(Order $order, string $successUrl, string $cancelUrl): ?Session
    {
        $connectId = self::getConnectId();

        if (! $connectId) {
            Log::warning('No Stripe Connect ID for checkout', ['order' => $order->id]);

            return null;
        }

        try {
            $lineItems = [];

            foreach ($order->orderItems as $item) {
                $product = $item->product;
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $product ? $product->name : 'Item',
                            'description' => $item->special_instructions ?: null,
                        ],
                        'unit_amount' => (int) round($item->unit_price * 100),
                    ],
                    'quantity' => $item->quantity,
                ];
            }

            // Add delivery fee as a line item if applicable
            if ($order->delivery_fee > 0) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Delivery Fee',
                        ],
                        'unit_amount' => (int) round($order->delivery_fee * 100),
                    ],
                    'quantity' => 1,
                ];
            }

            // Apply discount if any
            $discounts = [];
            if ($order->discount_amount > 0) {
                $coupon = $this->stripe->coupons->create([
                    'amount_off' => (int) round($order->discount_amount * 100),
                    'currency' => 'usd',
                    'duration' => 'once',
                    'name' => 'Order Discount',
                ], ['stripe_account' => $connectId]);

                $discounts[] = ['coupon' => $coupon->id];
            }

            $sessionParams = [
                'mode' => 'payment',
                'line_items' => $lineItems,
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'customer_email' => $order->customer?->email,
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'tenant_id' => tenant()->getTenantKey(),
                ],
                'payment_intent_data' => [
                    'metadata' => [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'tenant_id' => tenant()->getTenantKey(),
                    ],
                ],
            ];

            if (! empty($discounts)) {
                $sessionParams['discounts'] = $discounts;
            }

            $session = $this->stripe->checkout->sessions->create(
                $sessionParams,
                ['stripe_account' => $connectId]
            );

            // Store session ID on the order
            $order->update([
                'stripe_checkout_session_id' => $session->id,
                'payment_method' => PaymentMethod::Stripe,
                'payment_status' => PaymentStatus::Unpaid,
            ]);

            Log::info('Stripe checkout session created', [
                'order' => $order->order_number,
                'session_id' => $session->id,
                'connect_account' => $connectId,
            ]);

            return $session;

        } catch (\Exception $e) {
            Log::error('Stripe checkout session creation failed', [
                'order' => $order->order_number,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Verify a completed checkout session and mark order as paid.
     */
    public function handleCheckoutComplete(string $sessionId): ?Order
    {
        $connectId = self::getConnectId();

        if (! $connectId) {
            return null;
        }

        try {
            $session = $this->stripe->checkout->sessions->retrieve(
                $sessionId,
                ['expand' => ['payment_intent']],
                ['stripe_account' => $connectId]
            );

            if ($session->payment_status !== 'paid') {
                return null;
            }

            $order = Order::query()->where('stripe_checkout_session_id', $sessionId)->first();

            if (! $order) {
                Log::warning('No order found for checkout session', ['session_id' => $sessionId]);

                return null;
            }

            $order->update([
                'payment_status' => PaymentStatus::Paid,
                'stripe_payment_intent_id' => $session->payment_intent->id ?? $session->payment_intent,
            ]);

            Log::info('Order marked as paid via Stripe', ['order' => $order->order_number]);

            return $order;

        } catch (\Exception $e) {
            Log::error('Failed to verify checkout session', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
