<?php

namespace App\Services\Stripe;

use App\Actions\Stripe\HandleCheckoutComplete;
use App\Enums\Orders\OrderStatus;
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
    public function __construct(
        private readonly StripeSessionPayloadBuilder $payloadBuilder,
        private readonly StripeSettingsReader $settings,
        private readonly HandleCheckoutComplete $handleCheckoutComplete,
        private readonly StripeClient $stripe,
    ) {}

    public function redirectToCheckout(Order $order): ?string
    {
        if (! $order->total->isPositive() || ! $this->settings->isEnabled()) {
            return null;
        }

        return $this->createOrderSession($order)?->url;
    }

    /**
     * Whether the customer can still pay this order by card: a Stripe order nobody has
     * paid or cancelled, with an amount to pay, while the baker still takes cards.
     */
    public function canPayOnline(Order $order): bool
    {
        return $order->payment_method === PaymentMethod::Stripe
            && $order->payment_status === PaymentStatus::Unpaid
            && $order->status !== OrderStatus::Cancelled
            && $order->total->isPositive()
            && $this->settings->isEnabled();
    }

    /**
     * Where to send a customer who left Stripe without paying: the stored session's own
     * page while Stripe says it is open, the success page when it turns out to have been
     * paid, otherwise a new session. Null when the order can't be paid online or a new
     * session can't be made.
     */
    public function resumeOrCreateCheckout(Order $order): ?string
    {
        if (! $this->canPayOnline($order)) {
            return null;
        }

        $storedUrl = $this->resumeStoredSession($order);

        if ($storedUrl !== null) {
            return $storedUrl;
        }

        return $this->createOrderSession($order)?->url;
    }

    /**
     * The stored session's url while it is open, or the success url once it was paid.
     * A session that is finished any other way is forgotten, so the order can be edited
     * again if no replacement can be made. Null when a new session is needed.
     */
    private function resumeStoredSession(Order $order): ?string
    {
        $sessionId = $order->stripe_checkout_session_id;
        $connectId = $this->settings->connectId();

        if ($sessionId === null || $connectId === null) {
            return null;
        }

        try {
            $session = $this->stripe->checkout->sessions->retrieve($sessionId, [], ['stripe_account' => $connectId]);
        } catch (\Exception $e) {
            Log::warning('Could not read the stored order checkout session', [
                'order' => $order->order_number,
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        if ($session->status === 'open' && is_string($session->url)) {
            return $session->url;
        }

        if ($session->status === 'complete' && $session->payment_status === 'paid') {
            return route('order.stripe.success', $order)."?session_id={$sessionId}";
        }

        $order->update(['stripe_checkout_session_id' => null]);

        return null;
    }

    private function createOrderSession(Order $order): ?Session
    {
        return $this->createCheckoutSession(
            $order,
            route('order.stripe.success', $order).'?session_id={CHECKOUT_SESSION_ID}',
            route('order.stripe.cancel', $order),
        );
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

            return ($this->handleCheckoutComplete)($order, $paymentIntentId, (int) $session->amount_total);
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
        $amountOff = $order->discount_amount->add($order->gift_card_amount);

        if (! $amountOff->isPositive()) {
            return [];
        }

        $coupon = $this->stripe->coupons->create([
            'amount_off' => $amountOff->cents(),
            'currency' => Config::string('cashier.currency', 'usd'),
            'duration' => 'once',
            'name' => 'Order Discount',
        ], ['stripe_account' => $connectId]);

        return [['coupon' => $coupon->id]];
    }
}
