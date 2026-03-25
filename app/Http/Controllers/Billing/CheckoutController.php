<?php

namespace App\Http\Controllers\Billing;

use App\Enums\SubscriptionTier;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    /**
     * Redirect to Stripe Checkout for the selected plan.
     */
    public function __invoke(Request $request, string $plan): RedirectResponse
    {
        $request->validate(['plan' => [Rule::in(SubscriptionTier::cases())]]);

        $priceId = config("saas.stripe_prices.{$plan}");

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
