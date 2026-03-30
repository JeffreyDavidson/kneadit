<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\RedeemLoyaltyRewardRequest;
use App\Models\Customer;
use App\Models\LoyaltyReward;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;

class LoyaltyController extends Controller
{
    public function show(): View
    {
        $rewards = LoyaltyReward::query()->active()->orderBy('points_required')->get();
        $programName = settings('loyalty_program_name', 'Rewards');
        $pointsPerDollar = settings('loyalty_points_per_dollar', '10');
        $loyaltyEnabled = settings('loyalty_enabled', '1') === '1';

        $heroImage = settings('loyalty_hero_image');
        $heroImageUrl = $heroImage ? Storage::url($heroImage) : 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1920&q=80';
        $content = settingsPageContent('loyalty');

        $howSteps = $content['how_it_works_steps'] ?? [
            ['title' => 'Place an Order', 'description' => 'Order your favorite baked goods as usual.'],
            ['title' => 'Earn Points', 'description' => "Get {$pointsPerDollar} points for every \$1 spent when delivered."],
            ['title' => 'Redeem Rewards', 'description' => 'Use your points for discounts and free treats!'],
        ];
        $howSvgs = [
            'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z',
            'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
            'M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7',
        ];

        return view('loyalty', compact('rewards', 'programName', 'pointsPerDollar', 'loyaltyEnabled', 'heroImageUrl', 'content', 'howSteps', 'howSvgs'));
    }

    public function store(RedeemLoyaltyRewardRequest $request): View
    {

        $customer = Customer::query()->where('email', $request->email)->first();
        $rewards = LoyaltyReward::query()->active()->orderBy('points_required')->get();
        $programName = settings('loyalty_program_name', 'Rewards');
        $pointsPerDollar = settings('loyalty_points_per_dollar', '10');
        $loyaltyEnabled = settings('loyalty_enabled', '1') === '1';

        $totalPoints = $customer ? $customer->total_points : 0;
        $lifetimeEarned = $customer ? $customer->lifetime_points_earned : 0;
        $history = $customer ? $customer->loyaltyPoints()->latest('created_at')->limit(20)->get() : collect();

        $heroImage = settings('loyalty_hero_image');
        $heroImageUrl = $heroImage ? Storage::url($heroImage) : 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1920&q=80';
        $content = settingsPageContent('loyalty');

        $howSteps = $content['how_it_works_steps'] ?? [
            ['title' => 'Place an Order', 'description' => 'Order your favorite baked goods as usual.'],
            ['title' => 'Earn Points', 'description' => "Get {$pointsPerDollar} points for every \$1 spent when delivered."],
            ['title' => 'Redeem Rewards', 'description' => 'Use your points for discounts and free treats!'],
        ];
        $howSvgs = [
            'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z',
            'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
            'M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7',
        ];

        return view('loyalty', compact(
            'rewards', 'programName', 'pointsPerDollar', 'loyaltyEnabled',
            'customer', 'totalPoints', 'lifetimeEarned', 'history',
            'heroImageUrl', 'content', 'howSteps', 'howSvgs',
        ));
    }
}
