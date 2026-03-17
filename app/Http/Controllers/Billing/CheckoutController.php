<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    /**
     * Redirect to Stripe Checkout for the selected plan.
     */
    public function __invoke(Request $request, string $plan)
    {
        $request->validate(['plan' => ['in:starter,growth,pro']]);

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
