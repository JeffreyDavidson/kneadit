<?php

namespace App\Http\Controllers\Billing;

use App\Enums\SubscriptionTier;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Cashier\Checkout;

class CheckoutController extends Controller
{
    /**
     * Redirect to Stripe Checkout for the selected plan.
     */
    public function __invoke(Request $request, string $plan): Checkout
    {
        /** @var User $user */
        $user = $request->user();

        $tier = SubscriptionTier::tryFrom($plan);

        abort_unless((bool) $tier, 404, 'Plan not found.');

        $priceId = config("saas.stripe_prices.{$tier->value}");

        abort_unless((bool) $priceId, 404, 'Plan not found.');

        return $user
            ->newSubscription('default', $priceId)
            ->trialDays(config('saas.trial_days', 30))
            ->allowPromotionCodes()
            ->checkout([
                'success_url' => route('billing.success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('billing.plans'),
            ]);
    }
}
