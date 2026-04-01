<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\RedeemLoyaltyRewardRequest;
use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyReward;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;

class LoyaltyController extends Controller
{
    public function store(RedeemLoyaltyRewardRequest $request, TenantSettings $settings): View
    {
        $customer = Customer::query()->where('email', $request->email)->first();

        return view('storefront.loyalty', [
            ...$this->sharedViewData($settings),
            'customer' => $customer,
            'totalPoints' => $customer?->total_points ?? 0,
            'lifetimeEarned' => $customer?->lifetime_points_earned ?? 0,
            'history' => $customer?->loyaltyPoints()->latest('created_at')->limit(20)->get() ?? collect(),
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
