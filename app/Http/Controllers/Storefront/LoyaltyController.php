<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\RedeemLoyaltyRewardRequest;
use App\Models\Customers\Customer;
use App\Services\Loyalty\CustomerLoyalty;
use App\Services\Settings\TenantSettings;
use App\ViewModels\Storefront\LoyaltyPageViewModel;
use Illuminate\Contracts\View\View;

class LoyaltyController extends Controller
{
    public function store(RedeemLoyaltyRewardRequest $request, TenantSettings $settings, CustomerLoyalty $customerLoyalty): View
    {
        $customer = Customer::query()->where('email', $request->email)->first();

        $vm = $customer
            ? LoyaltyPageViewModel::forCustomer($settings, $customer, $customerLoyalty)
            : LoyaltyPageViewModel::notFound($settings);

        return view('storefront.loyalty', ['vm' => $vm]);
    }

    public function show(TenantSettings $settings): View
    {
        return view('storefront.loyalty', [
            'vm' => LoyaltyPageViewModel::empty($settings),
        ]);
    }
}
