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

        // The trial starts when the bakery is created, so the first charge
        // waits for the bakery trial to end. Once it has ended (or there is no
        // bakery), billing starts now.
        $trialEndsAt = $user->tenants()->first()?->trial_ends_at;

        if ($trialEndsAt?->isFuture()) {
            $builder->trialUntil($trialEndsAt);
        }

        return $builder
            ->allowPromotionCodes()
            ->checkout([
                'success_url' => route('billing.success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('billing.plans'),
            ]);
    }
}
