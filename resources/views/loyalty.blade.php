@extends('layouts.storefront')

@section('content')
@php
    $heroImage = settings('loyalty_hero_image');
    $heroImageUrl = $heroImage ? Storage::url($heroImage) : 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1920&q=80';

    $content = settingsPageContent('loyalty');
@endphp

<style>
    @keyframes loyaltyKenBurns { 0% { transform: scale(1); } 100% { transform: scale(1.06); } }
    @keyframes loyaltyFadeUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    .loyalty-hero-img { animation: loyaltyKenBurns 25s ease-in-out infinite alternate; }
    .loyalty-fade-1 { animation: loyaltyFadeUp 0.8s ease-out 0.3s both; }
    .loyalty-fade-2 { animation: loyaltyFadeUp 0.8s ease-out 0.5s both; }
    .loyalty-fade-3 { animation: loyaltyFadeUp 0.8s ease-out 0.7s both; }
</style>

{{-- Photo-Forward Hero --}}
<section class="relative overflow-hidden" style="min-height: 55vh;">
    <div class="absolute inset-0">
        <img src="{{ $heroImageUrl }}" alt="Fresh baked goods" class="w-full h-full object-cover loyalty-hero-img">
    </div>
    <div class="absolute inset-0" style="background: linear-gradient(to bottom, rgba(28,20,16,0.4) 0%, rgba(28,20,16,0.65) 50%, rgba(28,20,16,0.95) 100%);"></div>
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');"></div>

    <div class="relative z-10 flex flex-col justify-end min-h-[55vh] max-w-4xl mx-auto text-center px-4 pb-20">
        <div class="loyalty-fade-1 flex items-center justify-center gap-4 mb-6">
            <span class="block w-8 h-px" style="background: var(--warm-500); opacity: 0.4;"></span>
            <span class="uppercase tracking-[0.25em] text-xs font-semibold" style="color: var(--warm-500);">{{ $content['hero_eyebrow'] ?? 'Rewards Program' }}</span>
            <span class="block w-8 h-px" style="background: var(--warm-500); opacity: 0.4;"></span>
        </div>
        <h1 class="loyalty-fade-2 font-display text-4xl md:text-6xl font-bold mb-6 leading-tight" style="color: white;">
            {{ settings('store_name', 'Our') }} {{ $programName }}
        </h1>
        <p class="loyalty-fade-3 font-script text-2xl md:text-3xl" style="color: var(--warm-400);">
            {{ $content['hero_subtitle'] ?? 'Earn points with every order, unlock delicious rewards' }}
        </p>
    </div>
</section>

@if(!$loyaltyEnabled)
<section class="py-20 px-4" style="background: var(--warm-50);">
    <div class="max-w-lg mx-auto text-center rounded-2xl p-12" style="background: white; border: 1px solid var(--warm-200);">
        <p class="font-display text-xl" style="color: var(--warm-700);">{{ $content['paused_message'] ?? 'Our loyalty program is currently paused. Check back soon!' }}</p>
    </div>
</section>
@else

{{-- Check Points / Customer Dashboard --}}
<section class="py-20 px-4" style="background: var(--warm-50);">
    <div class="max-w-5xl mx-auto">

        {{-- Points Lookup --}}
        <div class="max-w-xl mx-auto mb-16">
            <div class="rounded-2xl p-8" style="background: white; border: 1px solid var(--warm-200); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.06);">
                <h2 class="font-display text-2xl font-bold mb-4 text-center" style="color: var(--warm-900);">{{ $content['check_heading'] ?? 'Check Your Points' }}</h2>
                <form action="{{ route('rewards.check') }}" method="POST" class="flex flex-col sm:flex-row gap-3">
                    @csrf
                    <input type="email" name="email" placeholder="Enter your email address"
                           value="{{ old('email', $customer->email ?? '') }}" required class="input-field flex-1">
                    <button type="submit" class="px-8 py-3 rounded-full font-semibold transition-all duration-300 hover:scale-105 whitespace-nowrap" style="background: var(--warm-500); color: var(--warm-900);">
                        Check Balance
                    </button>
                </form>
            </div>
        </div>

        {{-- Customer Results --}}
        @isset($customer)
        <div class="mb-16">
            <p class="font-script text-2xl text-center mb-8" style="color: var(--warm-500);">Welcome back, {{ $customer->name }}!</p>

            {{-- Points Display --}}
            <div class="grid sm:grid-cols-2 gap-6 max-w-2xl mx-auto mb-10">
                <div class="rounded-2xl p-8 text-center relative overflow-hidden" style="background: var(--warm-900);">
                    <div class="absolute top-0 right-0 w-32 h-32 rounded-full opacity-[0.06]" style="background: var(--warm-500); transform: translate(30%, -30%);"></div>
                    <p class="uppercase tracking-[0.25em] text-xs font-semibold mb-2" style="color: var(--warm-500);">Available Points</p>
                    <p class="font-display text-5xl font-bold" style="color: var(--warm-500);">{{ number_format($totalPoints) }}</p>
                </div>
                <div class="rounded-2xl p-8 text-center" style="background: white; border: 1px solid var(--warm-200);">
                    <p class="uppercase tracking-[0.25em] text-xs font-semibold mb-2" style="color: var(--warm-500);">Lifetime Earned</p>
                    <p class="font-display text-5xl font-bold" style="color: var(--warm-900);">{{ number_format($lifetimeEarned) }}</p>
                </div>
            </div>

            {{-- Progress to next reward --}}
            @if($rewards->count())
            @php
                $nextReward = $rewards->where('points_required', '>', $totalPoints)->sortBy('points_required')->first();
            @endphp
            @if($nextReward)
            <div class="max-w-2xl mx-auto mb-10 rounded-2xl p-6" style="background: white; border: 1px solid var(--warm-200);">
                <div class="flex justify-between items-center mb-3">
                    <span class="text-sm font-semibold" style="color: var(--warm-700);">Next Reward: {{ $nextReward->name }}</span>
                    <span class="text-sm font-bold" style="color: var(--warm-500);">{{ number_format($totalPoints) }} / {{ number_format($nextReward->points_required) }} pts</span>
                </div>
                <div class="w-full rounded-full h-3 overflow-hidden" style="background: var(--warm-200);">
                    <div class="h-full rounded-full transition-all duration-700" style="background: linear-gradient(90deg, var(--warm-500), var(--warm-400)); width: {{ min(100, ($totalPoints / $nextReward->points_required) * 100) }}%;"></div>
                </div>
                <p class="text-xs mt-2 text-right" style="color: var(--warm-500);">{{ number_format($nextReward->points_required - $totalPoints) }} points to go!</p>
            </div>
            @endif
            @endif

            {{-- Transaction History --}}
            @if($history->count())
            <div class="max-w-2xl mx-auto rounded-2xl overflow-hidden" style="background: white; border: 1px solid var(--warm-200);">
                <div class="px-6 py-4" style="border-bottom: 1px solid var(--warm-200);">
                    <h3 class="font-display text-lg font-bold" style="color: var(--warm-900);">Points History</h3>
                </div>
                <div class="divide-y" style="border-color: var(--warm-100);">
                    @foreach($history as $entry)
                    <div class="flex justify-between items-center px-6 py-4 hover:bg-opacity-50 transition-colors" style="hover: var(--warm-50);">
                        <div>
                            <p class="font-semibold" style="color: var(--warm-900);">{{ $entry->description }}</p>
                            <p class="text-sm" style="color: var(--warm-500);">{{ $entry->created_at->format('M j, Y') }}</p>
                        </div>
                        <span class="font-display text-lg font-bold {{ $entry->type === 'earned' ? 'text-green-600' : ($entry->type === 'redeemed' ? 'text-red-600' : 'text-yellow-600') }}">
                            {{ $entry->type === 'redeemed' ? '-' : '+' }}{{ number_format($entry->points) }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endisset

        @if(isset($customer) && !$customer)
        <div class="max-w-xl mx-auto mb-16 rounded-2xl p-8 text-center" style="background: white; border: 1px solid var(--warm-200);">
            <p style="color: var(--warm-700);">We couldn't find an account with that email. Points are earned automatically when your orders are delivered!</p>
        </div>
        @endif

        {{-- Available Rewards --}}
        @if($rewards->count())
        <div class="mb-16">
            <div class="text-center mb-10">
                <p class="uppercase tracking-[0.25em] text-xs font-semibold mb-2" style="color: var(--warm-500);">{{ $content['rewards_eyebrow'] ?? 'Unlock' }}</p>
                <h2 class="font-display text-3xl font-bold" style="color: var(--warm-900);">{{ $content['rewards_heading'] ?? 'Available Rewards' }}</h2>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-4xl mx-auto">
                @foreach($rewards as $reward)
                @php $canRedeem = isset($totalPoints) && $totalPoints >= $reward->points_required; @endphp
                <div class="rounded-2xl p-6 text-center transition-all duration-300 hover:shadow-xl hover:-translate-y-1 relative overflow-hidden"
                     style="background: white; border: 2px solid {{ $canRedeem ? 'var(--warm-500)' : 'var(--warm-200)' }};">
                    @if($canRedeem)
                    <div class="absolute top-3 right-3 px-2 py-1 rounded-full text-xs font-bold" style="background: var(--warm-500); color: var(--warm-900);">Redeemable!</div>
                    @endif
                    <div class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center" style="background: var(--warm-100);">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color: var(--warm-500);"><path stroke-linecap="round" stroke-linejoin="round" d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>
                    </div>
                    <h3 class="font-display text-lg font-bold mb-1" style="color: var(--warm-900);">{{ $reward->name }}</h3>
                    @if($reward->description)
                    <p class="text-sm mb-3" style="color: var(--warm-600);">{{ $reward->description }}</p>
                    @endif
                    <div class="inline-block px-4 py-2 rounded-full" style="background: var(--warm-100);">
                        <span class="font-display font-bold" style="color: var(--warm-800);">{{ number_format($reward->points_required) }}</span>
                        <span class="text-sm" style="color: var(--warm-500);">pts</span>
                    </div>
                    <p class="text-xs mt-2" style="color: var(--warm-500);">{{ $reward->reward_type_label }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</section>

{{-- How It Works --}}
<section class="relative overflow-hidden py-20 px-4" style="background: var(--warm-900);">
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');"></div>
    <div class="relative z-10 max-w-4xl mx-auto">
        <div class="text-center mb-12">
            <p class="uppercase tracking-[0.25em] text-xs font-semibold mb-2" style="color: var(--warm-500);">{{ $content['how_it_works_eyebrow'] ?? 'Simple as' }}</p>
            <h2 class="font-display text-3xl md:text-4xl font-bold" style="color: white;">{{ $content['how_it_works_heading'] ?? 'How It Works' }}</h2>
        </div>
        @php
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
        @endphp
        <div class="grid sm:grid-cols-3 gap-10 text-center">
            @foreach($howSteps as $i => $step)
            @php
                $stepDesc = str_replace('{{points_per_dollar}}', $pointsPerDollar, $step['description']);
            @endphp
            <div>
                <div class="w-20 h-20 rounded-full mx-auto mb-5 flex items-center justify-center" style="background: rgba(232,176,74,0.1); border: 1px solid rgba(232,176,74,0.2);">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color: var(--warm-500);"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $howSvgs[$i] ?? $howSvgs[0] }}"/></svg>
                </div>
                <h3 class="font-display text-xl font-bold mb-2" style="color: var(--warm-200);">{{ $step['title'] }}</h3>
                <p style="color: var(--warm-500);">{{ $stepDesc }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
