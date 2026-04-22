<?php

namespace App\Http\Controllers\Stripe;

use App\Actions\Stripe\InitiateStripeConnect;
use App\Filament\Pages\Platform\Onboarding;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class StripeConnectController extends Controller
{
    public function __invoke(InitiateStripeConnect $initiateConnect): RedirectResponse
    {
        $url = $initiateConnect(
            refreshUrl: Onboarding::getUrl(['stripe_connect' => 'refresh']),
            returnUrl: Onboarding::getUrl(['stripe_connect' => 'complete']),
        );

        return redirect($url);
    }
}
