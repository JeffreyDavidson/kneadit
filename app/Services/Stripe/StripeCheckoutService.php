<?php

namespace App\Services\Stripe;

use App\Actions\Orders\MarkOrderPaid;
use App\Enums\Orders\PaymentMethod;
use App\Enums\Orders\PaymentStatus;
use App\Models\Orders\Order;
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

    public static function isEnabled(): bool
    {
        $methods = settings('payment_methods');
        $methods = $methods ? json_decode($methods, true) : [];

        if (! in_array('stripe', $methods)) {
            return false;
        }

        $connectId = settings('stripe_connect_id');
        $chargesEnabled = settings('stripe_connect_charges_enabled', '0');

        return $connectId && $chargesEnabled === '1';
    }

    public static function getConnectId(): ?string
    {
        return settings('stripe_connect_id');
    }

    public function redirectToCheckout(Order $order): ?string
    {
        if ($order->total <= 0 || ! self::isEnabled()) {
            return null;
        }

        $session = $this->createCheckoutSession(
            $order,
            route('order.stripe.success', $order) . '?session_id={CHECKOUT_SESSION_ID}',
            route('order.stripe.cancel', $order),
        );

        return $session?->url;
    }

    public function createCheckoutSession(Order $order, string $successUrl, string $cancelUrl): ?Session
    {
        $connectId = self::getConnectId();

        if (! $connectId) {
            Log::warning('No Stripe Connect ID for checkout', ['order' => $order->id]);

            return null;
        }

        try {
            $lineItems = $this->buildLineItems($order);
            $discounts = $this->buildDiscounts($order, $connectId);
            $sessionParams = $this->buildSessionParams($order, $lineItems, $discounts, $successUrl, $cancelUrl);

            $session = $this->stripe->checkout->sessions->create(
                $sessionParams,
                ['stripe_account' => $connectId],
            );

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
                ['stripe_account' => $connectId],
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
                'stripe_payment_intent_id' => $session->payment_intent->id ?? $session->payment_intent,
            ]);

            app(MarkOrderPaid::class)($order);

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

    /** @return array<int, array<string, mixed>> */
    private function buildLineItems(Order $order): array
    {
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

        if ($order->delivery_fee > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => ['name' => 'Delivery Fee'],
                    'unit_amount' => (int) round($order->delivery_fee * 100),
                ],
                'quantity' => 1,
            ];
        }

        return $lineItems;
    }

    /** @return array<int, array<string, string>> */
    private function buildDiscounts(Order $order, string $connectId): array
    {
        if ($order->discount_amount <= 0) {
            return [];
        }

        $coupon = $this->stripe->coupons->create([
            'amount_off' => (int) round($order->discount_amount * 100),
            'currency' => 'usd',
            'duration' => 'once',
            'name' => 'Order Discount',
        ], ['stripe_account' => $connectId]);

        return [['coupon' => $coupon->id]];
    }

    /**
     * @param array<int, array<string, mixed>> $lineItems
     * @param array<int, array<string, string>> $discounts
     * @return array<string, mixed>
     */
    private function buildSessionParams(Order $order, array $lineItems, array $discounts, string $successUrl, string $cancelUrl): array
    {
        $params = [
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
            $params['discounts'] = $discounts;
        }

        return $params;
    }
}
