<?php

namespace App\Http\Controllers\Storefront\Account;

use App\Http\Controllers\Controller;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerFavorite;
use App\Services\Loyalty\CustomerLoyalty;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerDashboardController extends Controller
{
    public function __invoke(TenantSettings $settings, CustomerLoyalty $customerLoyalty): View
    {
        /** @var Customer $customer */
        $customer = auth('customer')->user();

        $customer->loadMissing([
            'orders' => fn (HasMany $q) => $q->latest()->limit(10),
            'orders.orderItems.product',
        ]);

        $favorites = CustomerFavorite::query()
            ->forCustomer($customer->email)
            ->with('product')
            ->latest()
            ->get();

        $loyaltyBalance = $settings->loyalty->enabled ? $customerLoyalty->balance($customer) : null;
        $loyaltyTier = $settings->loyalty->tiersEnabled ? $customerLoyalty->tier($customer) : null;

        $referralCode = $settings->engagement->customerReferralProgramEnabled
            ? $customer->referral_code
            : null;
        $referralShareUrl = $referralCode
            ? route('customer.referral', [
                'code' => $referralCode,
            ])
            : null;

        return view('storefront.account.dashboard', [
            'customer' => $customer,
            'orders' => $customer->orders,
            'favorites' => $favorites,
            'settings' => $settings,
            'loyaltyBalance' => $loyaltyBalance,
            'loyaltyTier' => $loyaltyTier,
            'referralCode' => $referralCode,
            'referralShareUrl' => $referralShareUrl,
        ]);
    }
}
