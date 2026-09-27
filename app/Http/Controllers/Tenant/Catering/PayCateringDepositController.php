<?php

namespace App\Http\Controllers\Tenant\Catering;

use App\Http\Controllers\Controller;
use App\Models\Customers\CateringInquiry;
use App\Services\Customers\CateringDepositCalculator;
use App\Services\Settings\TenantSettings;
use App\Services\Stripe\CateringDepositCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Config;

class PayCateringDepositController extends Controller
{
    public function __invoke(
        CateringInquiry $inquiry,
        TenantSettings $settings,
        CateringDepositCheckoutService $checkout,
        CateringDepositCalculator $depositCalculator,
    ): RedirectResponse {
        if ($inquiry->deposit_paid_at !== null) {
            return redirect()->away(Config::string('app.url'))
                ->with('success', 'Deposit already received — thank you!');
        }

        $depositDollars = $depositCalculator->suggestedAmount(
            $inquiry,
            $settings->catering->depositPercent,
        );

        abort_if($depositDollars <= 0, 404, 'No deposit configured for this quote.');

        $url = $checkout->redirectToCheckout($inquiry, $depositDollars);

        abort_if($url === null, 503, 'Online deposit payment is not currently available.');

        return redirect($url);
    }
}
