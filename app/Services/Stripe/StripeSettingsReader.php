<?php

namespace App\Services\Stripe;

use App\Services\Settings\SettingsManager;

/**
 * Reads Stripe-related settings with per-request memoization.
 *
 * StripeCheckoutService::isEnabled() and ::getConnectId() previously
 * each instantiated SettingsManager and re-queried the same keys on
 * every call. A single checkout flow could query stripe_connect_id 2x.
 * This reader fetches each value once and caches it for the lifetime
 * of the request (singleton-bound).
 */
class StripeSettingsReader
{
    private ?bool $isEnabled = null;

    private ?string $connectIdCache = null;

    private bool $connectIdLoaded = false;

    public function __construct(
        private SettingsManager $settings,
    ) {}

    public function isEnabled(): bool
    {
        if ($this->isEnabled !== null) {
            return $this->isEnabled;
        }

        $methods = $this->settings->get('payment_methods');
        $methods = $methods ? json_decode($methods, true) : [];

        if (! in_array('stripe', $methods)) {
            return $this->isEnabled = false;
        }

        $connectId = $this->connectId();
        $chargesEnabled = $this->settings->get('stripe_connect_charges_enabled', '0');

        return $this->isEnabled = $connectId !== null && $chargesEnabled === '1';
    }

    public function connectId(): ?string
    {
        if ($this->connectIdLoaded) {
            return $this->connectIdCache;
        }

        $this->connectIdLoaded = true;

        return $this->connectIdCache = $this->settings->get('stripe_connect_id');
    }
}
