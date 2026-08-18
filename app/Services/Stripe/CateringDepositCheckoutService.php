<?php

namespace App\Services\Stripe;

use App\Actions\Customers\RecordCateringDeposit;
use App\Models\Customers\CateringInquiry;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\StripeClient;

/**
 * Sibling to StripeCheckoutService — creates Stripe Checkout sessions for
 * catering inquiry deposits and verifies them on the redirect callback.
 *
 * Reuses StripeSettingsReader for connect-account / enabled lookup. The
 * deposit is rendered as a single line item; no coupons or discounts are
 * applied (catering deposits aren't promotional).
 */
class CateringDepositCheckoutService
{
    private StripeClient $stripe;

    public function __construct(
        private StripeSettingsReader $settings,
    ) {
        $this->stripe = new StripeClient(config('cashier.secret'));
    }

    public function redirectToCheckout(CateringInquiry $inquiry, float $depositDollars): ?string
    {
        if ($depositDollars <= 0 || ! $this->settings->isEnabled()) {
            return null;
        }

        $session = $this->createCheckoutSession(
            $inquiry,
            $depositDollars,
            route('catering.stripe.success', $inquiry) . '?session_id={CHECKOUT_SESSION_ID}',
            route('catering.stripe.cancel', $inquiry),
        );

        return $session?->url;
    }

    public function createCheckoutSession(
        CateringInquiry $inquiry,
        float $depositDollars,
        string $successUrl,
        string $cancelUrl,
    ): ?Session {
        $connectId = $this->settings->connectId();

        if (! $connectId) {
            Log::warning('No Stripe Connect ID for catering deposit', ['inquiry' => $inquiry->id]);

            return null;
        }

        try {
            $session = $this->stripe->checkout->sessions->create(
                [
                    'mode' => 'payment',
                    'success_url' => $successUrl,
                    'cancel_url' => $cancelUrl,
                    'customer_email' => $inquiry->customer_email,
                    'line_items' => [[
                        'quantity' => 1,
                        'price_data' => [
                            'currency' => config('cashier.currency', 'usd'),
                            'unit_amount' => (int) round($depositDollars * 100),
                            'product_data' => [
                                'name' => "Catering deposit — {$inquiry->event_type}",
                                'description' => trim('Event date: ' . $inquiry->event_date?->format('M j, Y')),
                            ],
                        ],
                    ]],
                    'metadata' => [
                        'catering_inquiry_id' => (string) $inquiry->id,
                        'tenant_id' => (string) tenant()->getTenantKey(),
                    ],
                ],
                ['stripe_account' => $connectId],
            );

            $inquiry->forceFill(['stripe_checkout_session_id' => $session->id])->save();

            Log::info('Catering deposit checkout session created', [
                'inquiry' => $inquiry->id,
                'session_id' => $session->id,
            ]);

            return $session;
        } catch (\Exception $e) {
            Log::error('Catering deposit Stripe session creation failed', [
                'inquiry' => $inquiry->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function handleCheckoutComplete(string $sessionId): ?CateringInquiry
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

            $inquiry = CateringInquiry::query()->where('stripe_checkout_session_id', $sessionId)->first();
            if (! $inquiry) {
                Log::warning('No catering inquiry for checkout session', ['session_id' => $sessionId]);

                return null;
            }

            $paymentIntent = $session->payment_intent;
            $paymentIntentId = is_object($paymentIntent) ? $paymentIntent->id : (string) $paymentIntent;

            $depositDollars = (int) ($session->amount_total ?? 0) / 100;

            return resolve(RecordCateringDeposit::class)(
                $inquiry,
                $depositDollars,
                $paymentIntentId !== '' ? $paymentIntentId : null,
            );
        } catch (\Exception $e) {
            Log::error('Failed to verify catering deposit checkout session', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
