<?php

namespace App\Http\Controllers\Tenant\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyReward;
use App\Services\Loyalty\CustomerLoyalty;
use App\Services\Settings\TenantSettings;
use App\ViewModels\Storefront\LoyaltyPageViewModel;
use Illuminate\Contracts\View\View;

class LoyaltyController extends Controller
{
    /**
     * Shows the program to everyone, and the points only to the signed-in customer
     * they belong to. The route's customer.verified middleware keeps unverified
     * customers out.
     */
    public function show(TenantSettings $settings, CustomerLoyalty $customerLoyalty): View
    {
        $rewards = LoyaltyReward::query()->forStorefront()->get();
        $customer = auth('customer')->user();

        if (! $customer instanceof Customer) {
            // The "Sign in" link on the page brings the visitor back here afterwards.
            redirect()->setIntendedUrl(route('storefront.rewards'));

            return view('tenant.storefront.loyalty', [
                'vm' => LoyaltyPageViewModel::empty($settings, $rewards),
            ]);
        }

        return view('tenant.storefront.loyalty', [
            'vm' => LoyaltyPageViewModel::forCustomer($settings, $customer, $customerLoyalty, $rewards),
        ]);
    }
}
