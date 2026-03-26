<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\RedeemLoyaltyRewardRequest;
use App\Models\Customer;
use App\Models\LoyaltyReward;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;

class LoyaltyController extends Controller
{
    public function show(): View
    {
        try {
            $rewards = LoyaltyReward::query()->active()->orderBy('points_required')->get();
        } catch (\Exception $e) {
            Log::error('Failed to load loyalty rewards', ['error' => $e->getMessage()]);
            $rewards = collect();
        }
        $programName = Setting::get('loyalty_program_name', 'Rewards');
        $pointsPerDollar = Setting::get('loyalty_points_per_dollar', '10');
        $loyaltyEnabled = Setting::get('loyalty_enabled', '1') === '1';

        return view('loyalty', compact('rewards', 'programName', 'pointsPerDollar', 'loyaltyEnabled'));
    }

    public function store(RedeemLoyaltyRewardRequest $request): View
    {

        $customer = Customer::query()->where('email', $request->email)->first();
        try {
            $rewards = LoyaltyReward::query()->active()->orderBy('points_required')->get();
        } catch (\Exception $e) {
            Log::error('Failed to load loyalty rewards', ['error' => $e->getMessage()]);
            $rewards = collect();
        }
        $programName = Setting::get('loyalty_program_name', 'Rewards');
        $pointsPerDollar = Setting::get('loyalty_points_per_dollar', '10');
        $loyaltyEnabled = Setting::get('loyalty_enabled', '1') === '1';

        $totalPoints = $customer ? $customer->total_points : 0;
        $lifetimeEarned = $customer ? $customer->lifetime_points_earned : 0;
        $history = $customer ? $customer->loyaltyPoints()->latest('created_at')->limit(20)->get() : collect();

        return view('loyalty', compact(
            'rewards', 'programName', 'pointsPerDollar', 'loyaltyEnabled',
            'customer', 'totalPoints', 'lifetimeEarned', 'history'
        ));
    }
}
