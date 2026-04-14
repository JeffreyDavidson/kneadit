<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\RedeemLoyaltyRewardRequest;
use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyReward;
use App\Services\Loyalty\CustomerLoyalty;
use App\Services\Settings\TenantSettings;
use App\ValueObjects\LoyaltyBalance;
use App\ViewModels\Storefront\LoyaltyPageViewModel;
use Illuminate\Contracts\View\View;

class LoyaltyController extends Controller
{
    public function show(TenantSettings $settings): View
    {
        $content = settingsPageContent('loyalty');

        $vm = new LoyaltyPageViewModel(
            settings: $settings,
            customer: null,
            balance: new LoyaltyBalance(earned: 0, redeemed: 0, adjusted: 0),
            history: collect(),
            rewards: LoyaltyReward::query()->active()->orderBy('points_required')->get(),
            content: $content,
            howSteps: $content['how_it_works_steps'] ?? config('kneadit.default_loyalty_steps'),
        );

        return view('storefront.loyalty', ['vm' => $vm]);
    }

    public function store(RedeemLoyaltyRewardRequest $request, TenantSettings $settings, CustomerLoyalty $customerLoyalty): View
    {
        $customer = Customer::query()->where('email', $request->email)->first();

        if ($customer) {
            $snapshot = $customerLoyalty->snapshot($customer);
            $balance = $snapshot['balance'];
            $history = $snapshot['history'];
        } else {
            $balance = new LoyaltyBalance(earned: 0, redeemed: 0, adjusted: 0);
            $history = collect();
        }

        $content = settingsPageContent('loyalty');

        $vm = new LoyaltyPageViewModel(
            settings: $settings,
            customer: $customer,
            balance: $balance,
            history: $history,
            rewards: LoyaltyReward::query()->active()->orderBy('points_required')->get(),
            content: $content,
            howSteps: $content['how_it_works_steps'] ?? config('kneadit.default_loyalty_steps'),
            customerNotFound: $customer === null,
        );

        return view('storefront.loyalty', ['vm' => $vm]);
    }
}
