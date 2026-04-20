<?php

namespace App\Services\Stripe;

use App\Actions\Stripe\HandleCheckoutComplete;
use App\Enums\Orders\PaymentMethod;
use App\Enums\Orders\PaymentStatus;
use App\Models\Orders\Order;
use App\Services\Settings\SettingsManager;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\StripeClient;

class StripeCheckoutService
{
    protected StripeClient $stripe;

    public function __construct(
        private StripeSessionPayloadBuilder $payloadBuilder,
    ) {
        $this->stripe = new StripeClient(config('cashier.secret'));
    }

    public static function isEnabled(): bool
    {
        $manager = app(SettingsManager::class);
        $methods = $manager->get('payment_methods');
        $methods = $methods ? json_decode($methods, true) : [];

        if (! in_array('stripe', $methods)) {
            return false;
        }

        $connectId = $manager->get('stripe_connect_id');
        $chargesEnabled = $manager->get('stripe_connect_charges_enabled', '0');

        return $connectId && $chargesEnabled === '1';
    }

    public static function getConnectId(): ?string
    {
        return app(SettingsManager::class)->get('stripe_connect_id');
    }

    public function redirectToCheckout(Order $order): ?string
    {
        if (! $order->total->isPositive() || ! self::isEnabled()) {
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
            $discounts = $this->buildDiscounts($order, $connectId);
            $sessionParams = $this->payloadBuilder->build(
                $order,
                (string) tenant()->getTenantKey(),
                $successUrl,
                $cancelUrl,
                $discounts,
            );

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

            $paymentIntent = $session->payment_intent;
            $paymentIntentId = is_object($paymentIntent) ? $paymentIntent->id : $paymentIntent;

            return app(HandleCheckoutComplete::class)($order, (string) ($paymentIntentId ?? ''));
        } catch (\Exception $e) {
            Log::error('Failed to verify checkout session', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /** @return array<int, array<string, string>> */
    private function buildDiscounts(Order $order, string $connectId): array
    {
        if (! $order->discount_amount->isPositive()) {
            return [];
        }

        $coupon = $this->stripe->coupons->create([
            'amount_off' => $order->discount_amount->cents(),
            'currency' => config('cashier.currency', 'usd'),
            'duration' => 'once',
            'name' => 'Order Discount',
        ], ['stripe_account' => $connectId]);

        return [['coupon' => $coupon->id]];
    }
}
