<?php

namespace App\Http\Controllers\Billing;

use App\Enums\Platform\SubscriptionTier;
use App\Http\Controllers\Controller;
use App\Models\Staff\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Support\Facades\Config;
use Laravel\Cashier\Checkout;

class CheckoutController extends Controller
{
    public function __invoke(#[CurrentUser] User $user, string $plan): Checkout
    {
        $tier = SubscriptionTier::tryFrom($plan);
        abort_unless($tier !== null, 404, 'Plan not found.');

        $priceKey = "kneadit.stripe_prices.{$tier->value}";
        $priceId = Config::get($priceKey);
        abort_unless(is_string($priceId) && $priceId !== '', 404, 'Plan not found.');

        return $user
            ->newSubscription('default', $priceId)
            ->trialDays(Config::integer('kneadit.trial_days', 30))
            ->allowPromotionCodes()
            ->checkout([
                'success_url' => route('billing.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('billing.plans'),
            ]);
    }
}
