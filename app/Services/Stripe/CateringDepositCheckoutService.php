<?php

namespace App\Services\Stripe;

use App\Actions\Customers\ApplyCateringDepositPayment;
use App\Models\Customers\CateringInquiry;
use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
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
    public function __construct(
        private readonly StripeSettingsReader $settings,
        private readonly ApplyCateringDepositPayment $applyCateringDepositPayment,
        private readonly StripeClient $stripe,
    ) {}

    public function redirectToCheckout(CateringInquiry $inquiry, float $depositDollars): ?string
    {
        if ($depositDollars <= 0 || ! $this->settings->isEnabled()) {
            return null;
        }

        $existingUrl = $this->resumeStoredSession($inquiry);

        if ($existingUrl !== null) {
            return $existingUrl;
        }

        // The success URL is signed without the session id; Stripe appends it afterwards and the
        // route's signature check ignores that one parameter.
        $session = $this->createCheckoutSession(
            $inquiry,
            $depositDollars,
            URL::signedRoute('catering.stripe.success', $inquiry).'&session_id={CHECKOUT_SESSION_ID}',
            URL::signedRoute('catering.stripe.cancel', $inquiry),
        );

        return $session?->url;
    }

    /**
     * Where to send the customer when the inquiry already has a Checkout session: its own
     * page while it is still open, or the success page once it was paid. Null when a new
     * session is needed (none stored, expired, or it can't be read).
     */
    private function resumeStoredSession(CateringInquiry $inquiry): ?string
    {
        $sessionId = $inquiry->stripe_checkout_session_id;
        $connectId = $this->settings->connectId();

        if ($sessionId === null || ! $connectId) {
            return null;
        }

        try {
            $session = $this->stripe->checkout->sessions->retrieve(
                $sessionId,
                ['expand' => ['payment_intent']],
                ['stripe_account' => $connectId],
            );
        } catch (\Exception $e) {
            Log::warning('Could not read the stored catering deposit checkout session', [
                'inquiry' => $inquiry->id,
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        if ($session->status === 'open' && is_string($session->url)) {
            return $session->url;
        }

        if ($session->status === 'complete' && $session->payment_status === 'paid') {
            $this->completeSession($session);

            return URL::signedRoute('catering.stripe.success', $inquiry)."&session_id={$session->id}";
        }

        return null;
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

        $tenant = tenant();

        if (! $tenant instanceof Tenant) {
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
                            'currency' => $this->configString('cashier.currency', 'usd'),
                            'unit_amount' => (int) round($depositDollars * 100),
                            'product_data' => [
                                'name' => "Catering deposit — {$inquiry->event_type}",
                                'description' => trim('Event date: '.$inquiry->event_date?->format('M j, Y')),
                            ],
                        ],
                    ]],
                    'metadata' => [
                        'catering_inquiry_id' => (string) $inquiry->id,
                        'tenant_id' => $tenant->id,
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

            return $this->completeSession($session);
        } catch (\Exception $e) {
            Log::error('Failed to verify catering deposit checkout session', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Records a paid session on the inquiry named in its metadata. The inquiry's stored
     * session id is not consulted: the customer may have paid an earlier session.
     */
    private function completeSession(Session $session): ?CateringInquiry
    {
        if ($session->payment_status !== 'paid') {
            return null;
        }

        $inquiryId = data_get($session, 'metadata.catering_inquiry_id');
        $inquiry = is_numeric($inquiryId) ? CateringInquiry::query()->find($inquiryId) : null;

        if (! $inquiry) {
            Log::warning('No catering inquiry for checkout session', ['session_id' => $session->id]);

            return null;
        }

        $paymentIntent = $session->payment_intent;
        $paymentIntentId = is_object($paymentIntent) ? $paymentIntent->id : (string) $paymentIntent;

        return ($this->applyCateringDepositPayment)(
            $inquiry,
            $session->id,
            $paymentIntentId !== '' ? $paymentIntentId : null,
            (int) ($session->amount_total ?? 0) / 100,
        );
    }

    private function configString(string $key, string $default = ''): string
    {
        return Config::string($key, $default);
    }
}
