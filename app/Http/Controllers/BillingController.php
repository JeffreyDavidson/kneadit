<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BillingController extends Controller
{
    /**
     * Show the plan selection page.
     */
    public function plans(Request $request)
    {
        return view('billing.plans', [
            'plans' => config('saas.plans'),
            'currentPlan' => $request->user()?->currentPlan(),
            'bakeryName' => session('bakery_name'),
        ]);
    }

    /**
     * Redirect to Stripe Checkout for the selected plan.
     */
    public function checkout(Request $request, string $plan)
    {
        $request->validate(['plan' => 'in:starter,growth,pro']);

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

    /**
     * Handle successful checkout.
     */
    public function success(Request $request)
    {
        // Re-authenticate user from Stripe session (session may be lost after external redirect)
        if (! $request->user() && $request->has('session_id')) {
            $checkoutSession = \Laravel\Cashier\Cashier::stripe()->checkout->sessions->retrieve($request->get('session_id'));

            if ($checkoutSession && $checkoutSession->customer) {
                $user = \App\Models\User::where('stripe_id', $checkoutSession->customer)->first();

                if ($user) {
                    \Illuminate\Support\Facades\Auth::login($user);
                }
            }
        }

        return redirect('/onboarding');
    }

    /**
     * Redirect to Stripe Customer Portal for managing subscription.
     */
    public function portal(Request $request)
    {
        return $request->user()->redirectToBillingPortal(route('filament.admin.pages.dashboard'));
    }

    /**
     * Swap to a different plan.
     */
    public function swap(Request $request, string $plan)
    {
        $priceId = config("saas.stripe_prices.{$plan}");

        abort_unless($priceId, 404, 'Plan not found.');

        $request->user()->subscription('default')->swap($priceId);

        return redirect()->route('billing.plans')
            ->with('success', 'Your plan has been updated!');
    }
}
