<?php

namespace App\Actions\Stripe;

use App\Models\Platform\Tenant;
use App\Services\Settings\SettingsManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class InitiateStripeConnect
{
    private StripeClient $stripe;

    public function __construct(
        private SettingsManager $settings,
    ) {
        $secret = Config::string('cashier.secret');

        if ($secret === '') {
            throw new \UnexpectedValueException('A Stripe secret is required to initiate Connect onboarding.');
        }

        $this->stripe = new StripeClient($secret);
    }

    public function __invoke(string $refreshUrl, string $returnUrl): string
    {
        $connectId = $this->resolveConnectAccount();

        $accountLink = $this->stripe->accountLinks->create([
            'account' => $connectId,
            'refresh_url' => $refreshUrl,
            'return_url' => $returnUrl,
            'type' => 'account_onboarding',
        ]);

        return $accountLink->url;
    }

    private function resolveConnectAccount(): string
    {
        $connectId = $this->settings->get('stripe_connect_id');

        if (is_string($connectId) && $connectId !== '') {
            return $connectId;
        }

        if ($connectId !== null && $connectId !== '') {
            throw new \UnexpectedValueException('The stored Stripe Connect account ID must be a string.');
        }

        $tenant = tenant();

        if (! $tenant instanceof Tenant) {
            throw new \UnexpectedValueException('Stripe Connect onboarding requires an active tenant.');
        }

        $account = $this->stripe->accounts->create([
            'type' => 'standard',
            'country' => 'US',
            'email' => $tenant->email,
            'business_type' => 'individual',
            'metadata' => [
                'tenant_id' => $tenant->id,
            ],
        ]);

        if ($account->id === '') {
            throw new \UnexpectedValueException('Stripe returned an invalid Connect account ID.');
        }

        $this->settings->set('stripe_connect_id', $account->id);

        Log::info('Stripe Connect account created', [
            'tenant' => $tenant->id,
            'connect_id' => $account->id,
        ]);

        return $account->id;
    }
}
