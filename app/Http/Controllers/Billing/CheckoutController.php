<?php

namespace App\Http\Controllers\Billing;

use App\Enums\SubscriptionTier;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    /**
     * Redirect to Stripe Checkout for the selected plan.
     */
    public function __invoke(Request $request, string $plan): RedirectResponse
    {
        $tier = SubscriptionTier::tryFrom($plan);

        abort_unless($tier, 404, 'Plan not found.');

        $priceId = config("saas.stripe_prices.{$tier->value}");

        abort_unless($priceId, 404, 'Plan not found.');

        return $request->user()
            ->newSubscription('default', $priceId)
            ->trialDays(config('saas.trial_days', 30))
            ->allowPromotionCodes()
            ->checkout([
                'success_url' => route('billing.success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('billing.plans'),
            ]);
    }
}
