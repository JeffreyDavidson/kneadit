<?php

namespace App\Services\Stripe;

use App\Actions\Stripe\HandleCheckoutComplete;
use App\Enums\Orders\PaymentMethod;
use App\Enums\Orders\PaymentStatus;
use App\Models\Orders\Order;
use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\StripeClient;

class StripeCheckoutService
{
    protected StripeClient $stripe;

    public function __construct(
        private StripeSessionPayloadBuilder $payloadBuilder,
        private StripeSettingsReader $settings,
    ) {
        $this->stripe = new StripeClient(Config::string('cashier.secret', ''));
    }

    public function redirectToCheckout(Order $order): ?string
    {
        if (! $order->total->isPositive() || ! $this->settings->isEnabled()) {
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
        $connectId = $this->settings->connectId();

        if (! $connectId) {
            Log::warning('No Stripe Connect ID for checkout', ['order' => $order->id]);

            return null;
        }

        $tenant = tenant();

        if (! $tenant instanceof Tenant) {
            return null;
        }

        try {
            $discounts = $this->buildDiscounts($order, $connectId);
            $sessionParams = $this->payloadBuilder->build(
                $order,
                $tenant->id,
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
        $connectId = $this->settings->connectId();

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

            return resolve(HandleCheckoutComplete::class)($order, (string) ($paymentIntentId ?? ''));
        } catch (\Exception $e) {
            Log::error('Failed to verify checkout session', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /** @return list<array{coupon: string}> */
    private function buildDiscounts(Order $order, string $connectId): array
    {
        if (! $order->discount_amount->isPositive()) {
            return [];
        }

        $coupon = $this->stripe->coupons->create([
            'amount_off' => $order->discount_amount->cents(),
            'currency' => $this->currency(),
            'duration' => 'once',
            'name' => 'Order Discount',
        ], ['stripe_account' => $connectId]);

        return [['coupon' => $coupon->id]];
    }

    private function currency(): string
    {
        return Config::string('cashier.currency', 'usd');
    }
}
