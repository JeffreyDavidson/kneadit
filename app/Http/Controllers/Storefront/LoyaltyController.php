<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\RedeemLoyaltyRewardRequest;
use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyReward;
use App\Services\Loyalty\CustomerLoyalty;
use App\Services\Settings\TenantSettings;
use App\ValueObjects\LoyaltyBalance;
use Illuminate\Contracts\View\View;

class LoyaltyController extends Controller
{
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

        return view('storefront.loyalty', [
            ...$this->sharedViewData($settings),
            'customer' => $customer,
            'totalPoints' => $balance->total,
            'lifetimeEarned' => $balance->earned,
            'history' => $history,
        ]);
    }

    public function show(TenantSettings $settings): View
    {
        return view('storefront.loyalty', $this->sharedViewData($settings));
    }

    /** @return array<string, mixed> */
    private function sharedViewData(TenantSettings $settings): array
    {
        $content = settingsPageContent('loyalty');

        return [
            'settings' => $settings,
            'rewards' => LoyaltyReward::query()->active()->orderBy('points_required')->get(),
            'content' => $content,
            'howSteps' => $content['how_it_works_steps'] ?? config('kneadit.default_loyalty_steps'),
        ];
    }
}
