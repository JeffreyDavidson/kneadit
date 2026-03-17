<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class StripeConnectController extends Controller
{
    /**
     * Redirect baker to Stripe Connect onboarding.
     */
    public function __invoke(Request $request)
    {
        $tenant = tenant();

        abort_unless($tenant, 404);

        // Create a Stripe Connect account for this tenant
        $stripe = new StripeClient(config('cashier.secret'));

        // Check if tenant already has a connect account
        $connectId = Setting::get('stripe_connect_id');

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
            Setting::set('stripe_connect_id', $connectId);
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

    /**
     * Check the status of a Stripe Connect account.
     */
    public static function getAccountStatus(): ?array
    {
        $connectId = Setting::get('stripe_connect_id');

        if (! $connectId) {
            return null;
        }

        try {
            $stripe = new StripeClient(config('cashier.secret'));
            $account = $stripe->accounts->retrieve($connectId);

            return [
                'id' => $account->id,
                'charges_enabled' => $account->charges_enabled,
                'payouts_enabled' => $account->payouts_enabled,
                'details_submitted' => $account->details_submitted,
                'business_profile' => $account->business_profile?->name,
            ];
        } catch (\Exception $e) {
            Log::warning('Stripe Connect account check failed', [
                'connect_id' => $connectId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
