<?php

namespace App\Http\Controllers\Billing;

use App\Enums\Platform\SubscriptionTier;
use App\Http\Controllers\Controller;
use App\Models\Staff\User;
use Illuminate\Container\Attributes\CurrentUser;
use Laravel\Cashier\Checkout;

class CheckoutController extends Controller
{
    public function __invoke(#[CurrentUser] User $user, string $plan): Checkout
    {
        $tier = SubscriptionTier::tryFrom($plan);
        abort_unless($tier, 404, 'Plan not found.');

        $priceId = config("kneadit.stripe_prices.{$tier->value}");
        abort_unless($priceId, 404, 'Plan not found.');

        return $user
            ->newSubscription('default', $priceId)
            ->trialDays(config('kneadit.trial_days', 30))
            ->allowPromotionCodes()
            ->checkout([
                'success_url' => route('billing.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('billing.plans'),
            ]);
    }
}
