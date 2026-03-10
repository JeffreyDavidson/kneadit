@extends('layouts.storefront')

@section('content')
{{-- Dark Hero --}}
<section class="relative overflow-hidden" style="background: var(--warm-900);">
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');"></div>
    <div class="relative z-10 max-w-4xl mx-auto text-center px-4 py-24 md:py-32">
        <div class="flex items-center justify-center gap-4 mb-6">
            <span class="block w-16 h-px" style="background: var(--warm-500); opacity: 0.4;"></span>
            <span class="uppercase tracking-[0.25em] text-xs font-semibold" style="color: var(--warm-500);">Rewards Program</span>
            <span class="block w-16 h-px" style="background: var(--warm-500); opacity: 0.4;"></span>
        </div>
        <h1 class="font-display text-4xl md:text-6xl font-bold mb-6 leading-tight" style="color: white;">
            {{ \App\Models\Setting::get('store_name', 'Our') }} {{ $programName }}
        </h1>
        <p class="font-script text-xl md:text-2xl" style="color: var(--warm-400);">
            Earn points with every order, unlock delicious rewards
        </p>
    </div>
</section>

@if(!$loyaltyEnabled)
<section class="py-20 px-4" style="background: var(--warm-50);">
    <div class="max-w-lg mx-auto text-center rounded-2xl p-12" style="background: white; border: 1px solid var(--warm-200);">
        <p class="font-display text-xl" style="color: var(--warm-700);">Our loyalty program is currently paused. Check back soon!</p>
    </div>
</section>
@else

{{-- Check Points / Customer Dashboard --}}
<section class="py-20 px-4" style="background: var(--warm-50);">
    <div class="max-w-5xl mx-auto">

        {{-- Points Lookup --}}
        <div class="max-w-xl mx-auto mb-16">
            <div class="rounded-2xl p-8" style="background: white; border: 1px solid var(--warm-200); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.06);">
                <h2 class="font-display text-2xl font-bold mb-4 text-center" style="color: var(--warm-900);">Check Your Points</h2>
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
                <p class="uppercase tracking-[0.25em] text-xs font-semibold mb-2" style="color: var(--warm-500);">Unlock</p>
                <h2 class="font-display text-3xl font-bold" style="color: var(--warm-900);">Available Rewards</h2>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-4xl mx-auto">
                @foreach($rewards as $reward)
                @php $canRedeem = isset($totalPoints) && $totalPoints >= $reward->points_required; @endphp
                <div class="rounded-2xl p-6 text-center transition-all duration-300 hover:shadow-xl hover:-translate-y-1 relative overflow-hidden"
                     style="background: white; border: 2px solid {{ $canRedeem ? 'var(--warm-500)' : 'var(--warm-200)' }};">
                    @if($canRedeem)
                    <div class="absolute top-3 right-3 px-2 py-1 rounded-full text-xs font-bold" style="background: var(--warm-500); color: var(--warm-900);">Redeemable!</div>
                    @endif
                    <div class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center text-2xl" style="background: var(--warm-100);">🎁</div>
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
            <p class="uppercase tracking-[0.25em] text-xs font-semibold mb-2" style="color: var(--warm-500);">Simple as</p>
            <h2 class="font-display text-3xl md:text-4xl font-bold" style="color: white;">How It Works</h2>
        </div>
        <div class="grid sm:grid-cols-3 gap-10 text-center">
            @foreach([
                ['🛒', 'Place an Order', 'Order your favorite baked goods as usual.'],
                ['⭐', 'Earn Points', "Get {$pointsPerDollar} points for every \$1 spent when delivered."],
                ['🎉', 'Redeem Rewards', 'Use your points for discounts and free treats!'],
            ] as [$icon, $title, $desc])
            <div>
                <div class="w-20 h-20 rounded-full mx-auto mb-5 flex items-center justify-center text-3xl" style="background: rgba(232,176,74,0.1); border: 1px solid rgba(232,176,74,0.2);">{{ $icon }}</div>
                <h3 class="font-display text-xl font-bold mb-2" style="color: var(--warm-200);">{{ $title }}</h3>
                <p style="color: var(--warm-500);">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
