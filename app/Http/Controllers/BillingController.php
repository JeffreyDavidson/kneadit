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
        ]);
    }

    /**
     * Redirect to Stripe Checkout for the selected plan.
     */
    public function checkout(Request $request, string $plan)
    {
        $request->validate(['plan' => 'in:starter,growth,pro']);

        $priceId = match ($plan) {
            'starter' => env('STRIPE_PRICE_STARTER'),
            'growth' => env('STRIPE_PRICE_GROWTH'),
            'pro' => env('STRIPE_PRICE_PRO'),
        };

        return $request->user()
            ->newSubscription('default', $priceId)
            ->trialDays(config('saas.trial_days', 30))
            ->allowPromotionCodes()
            ->checkout([
                'success_url' => route('billing.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('billing.plans'),
            ]);
    }

    /**
     * Handle successful checkout.
     */
    public function success(Request $request)
    {
        return view('billing.success');
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
        $priceId = match ($plan) {
            'starter' => env('STRIPE_PRICE_STARTER'),
            'growth' => env('STRIPE_PRICE_GROWTH'),
            'pro' => env('STRIPE_PRICE_PRO'),
            default => abort(404),
        };

        $request->user()->subscription('default')->swap($priceId);

        return redirect()->route('billing.plans')
            ->with('success', 'Your plan has been updated!');
    }
}
