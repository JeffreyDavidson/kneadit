<?php

namespace App\Http\Controllers\Billing;

use App\Actions\Stripe\ReauthenticateFromCheckoutSession;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CheckoutSuccessController extends Controller
{
    public function __invoke(Request $request, ReauthenticateFromCheckoutSession $reauthenticate): RedirectResponse
    {
        if (! $request->user() && $request->has('session_id')) {
            $reauthenticate($request->input('session_id'));
        }

        return to_route('onboarding.show');
    }
}
