<?php

namespace App\Http\Controllers\Billing;

use App\Enums\Platform\SubscriptionTier;
use App\Http\Controllers\Controller;
use App\Models\Staff\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Config;
use Laravel\Cashier\Checkout;

class CheckoutController extends Controller
{
    public function __invoke(#[CurrentUser] User $user, string $plan): Checkout|RedirectResponse
    {
        $tier = SubscriptionTier::tryFrom($plan);
        abort_unless($tier !== null, 404, 'Plan not found.');

        $priceId = Config::get("kneadit.stripe_prices.{$tier->value}");
        abort_unless(is_string($priceId) && $priceId !== '', 404, 'Plan not found.');

        // A bakery the platform comps has no plan to buy.
        if ($user->tenants()->where('free_forever', true)->exists()) {
            return to_route('billing.plans')
                ->with('error', 'Your bakery has a complimentary plan, so there is nothing to subscribe to.');
        }

        if ($user->subscribed('default')) {
            return to_route('billing.plans')
                ->with('error', 'You already have a subscription. Use Switch to change plans.');
        }

        $builder = $user->newSubscription('default', $priceId);

        // The trial is for first-time subscribers only.
        if (! $user->subscriptions()->where('type', 'default')->exists()) {
            $builder->trialDays(Config::integer('kneadit.trial_days', 30));
        }

        return $builder
            ->allowPromotionCodes()
            ->checkout([
                'success_url' => route('billing.success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('billing.plans'),
            ]);
    }
}
