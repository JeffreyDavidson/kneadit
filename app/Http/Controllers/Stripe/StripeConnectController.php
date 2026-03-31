<?php

namespace App\Http\Controllers\Stripe;

use App\Actions\Stripe\InitiateStripeConnect;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class StripeConnectController extends Controller
{
    public function __invoke(InitiateStripeConnect $initiateConnect): RedirectResponse
    {
        $url = $initiateConnect(
            refreshUrl: url('/admin/onboarding?stripe_connect=refresh'),
            returnUrl: url('/admin/onboarding?stripe_connect=complete'),
        );

        return redirect($url);
    }
}
