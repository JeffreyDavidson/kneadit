<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Stripe\StripeClient;

class StripeConnectController extends Controller
{
    /**
     * Redirect baker to Stripe Connect onboarding.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $tenant = tenant();

        abort_unless($tenant, 404);

        // Create a Stripe Connect account for this tenant
        $stripe = new StripeClient(config('cashier.secret'));

        // Check if tenant already has a connect account
        $connectId = settings('stripe_connect_id');

        if (! $connectId) {
            $account = $stripe->accounts->create([
                'type' => 'standard',
                'country' => 'US',
                'email' => $tenant->email,
                'business_type' => 'individual',
                'metadata' => [
                    'tenant_id' => $tenant->id,
                ],
            ]);

            $connectId = $account->id;
            settings(['stripe_connect_id' => $connectId]);
        }

        // Create an account link for onboarding
        $accountLink = $stripe->accountLinks->create([
            'account' => $connectId,
            'refresh_url' => url('/admin/onboarding?stripe_connect=refresh'),
            'return_url' => url('/admin/onboarding?stripe_connect=complete'),
            'type' => 'account_onboarding',
        ]);

        return redirect($accountLink->url);
    }
}
