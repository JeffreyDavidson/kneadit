<?php

namespace App\Http\Controllers\Catering;

use App\Http\Controllers\Controller;
use App\Models\Customers\CateringInquiry;
use App\Services\Stripe\CateringDepositCheckoutService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CateringStripeSuccessController extends Controller
{
    public function __invoke(
        CateringInquiry $inquiry,
        Request $request,
        CateringDepositCheckoutService $checkout,
    ): View {
        $sessionId = (string) $request->query('session_id', '');

        if ($sessionId !== '' && $inquiry->deposit_paid_at === null) {
            $checkout->handleCheckoutComplete($sessionId);
            $inquiry->refresh();
        }

        return view('storefront.catering.deposit-success', [
            'inquiry' => $inquiry,
            'paid' => $inquiry->deposit_paid_at !== null,
        ]);
    }
}
