<?php

namespace App\Actions\Stripe;

use App\Services\Settings\SettingsManager;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class InitiateStripeConnect
{
    private StripeClient $stripe;

    public function __construct(
        private SettingsManager $settings,
    ) {
        $this->stripe = new StripeClient(config('cashier.secret'));
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

        if ($connectId) {
            return $connectId;
        }

        $tenant = tenant();

        $account = $this->stripe->accounts->create([
            'type' => 'standard',
            'country' => 'US',
            'email' => $tenant->email,
            'business_type' => 'individual',
            'metadata' => [
                'tenant_id' => $tenant->id,
            ],
        ]);

        $this->settings->set('stripe_connect_id', $account->id);

        Log::info('Stripe Connect account created', [
            'tenant' => $tenant->id,
            'connect_id' => $account->id,
        ]);

        return $account->id;
    }
}
