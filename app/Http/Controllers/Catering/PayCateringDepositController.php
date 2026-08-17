<?php

namespace App\Http\Controllers\Catering;

use App\Actions\Customers\RecordCateringDeposit;
use App\Http\Controllers\Controller;
use App\Models\Customers\CateringInquiry;
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
        RecordCateringDeposit $depositAction,
    ): RedirectResponse {
        if ($inquiry->deposit_paid_at !== null) {
            return redirect()->away(Config::string('app.url'))
                ->with('success', 'Deposit already received — thank you!');
        }

        $depositDollars = $depositAction->suggestedAmount(
            $inquiry,
            $settings->catering->depositPercent,
        );

        abort_if($depositDollars <= 0, 404, 'No deposit configured for this quote.');

        $url = $checkout->redirectToCheckout($inquiry, $depositDollars);

        abort_if($url === null, 503, 'Online deposit payment is not currently available.');

        return redirect($url);
    }
}
