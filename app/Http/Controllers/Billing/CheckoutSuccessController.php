<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Staff\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Cashier\Cashier;

class CheckoutSuccessController extends Controller
{
    /**
     * Handle successful checkout.
     *
     * Re-authenticates the user from a verified Stripe checkout session
     * when the Laravel session is lost after the external redirect.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        if (! $request->user() && $request->has('session_id')) {
            $checkoutSession = Cashier::stripe()->checkout->sessions->retrieve($request->input('session_id'));

            if ($checkoutSession->status === 'complete'
                && $checkoutSession->customer
                && $checkoutSession->created >= now()->subMinutes(30)->getTimestamp()
            ) {
                $user = User::query()->where('stripe_id', $checkoutSession->customer)->first();

                if ($user) {
                    Auth::login($user);
                }
            }
        }

        return redirect('/onboarding');
    }
}
