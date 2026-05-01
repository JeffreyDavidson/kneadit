<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Customers\Customer;
use App\Services\Settings\TenantSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ApplyReferralCodeController extends Controller
{
    public function __invoke(string $code, Request $request, TenantSettings $settings): RedirectResponse
    {
        if (! $settings->engagement->customerReferralProgramEnabled) {
            return to_route('order.create');
        }

        $referrer = Customer::query()->forReferralCode($code)->first();

        if (! $referrer) {
            return to_route('order.create')
                ->withErrors(['referral' => 'Sorry, that referral link is no longer valid.']);
        }

        $request->session()->put('referral_code', $code);

        return to_route('order.create')
            ->with('success', "You'll get \${$settings->engagement->customerReferralDiscountDollars} off your first order, courtesy of {$referrer->name}.");
    }
}
