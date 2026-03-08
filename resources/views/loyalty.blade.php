@extends('layouts.storefront')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12">
    {{-- Header --}}
    <div class="text-center mb-12">
        <p class="font-script text-xl mb-2" style="color: var(--warm-500);">{{ $programName }}</p>
        <h1 class="font-display text-4xl md:text-5xl font-bold mb-4" style="color: var(--warm-900);">
            {{ \App\Models\Setting::get('store_name', 'Our') }} {{ $programName }}
        </h1>
        <p class="text-lg max-w-2xl mx-auto" style="color: var(--warm-700);">
            Earn points with every order and redeem them for delicious rewards!
        </p>
    </div>

    @if(!$loyaltyEnabled)
    <div class="text-center p-8 rounded-xl mb-8" style="background: var(--warm-100);">
        <p class="text-lg" style="color: var(--warm-700);">Our loyalty program is currently paused. Check back soon!</p>
    </div>
    @else

    {{-- Check Points Form --}}
    <div class="card p-6 mb-10">
        <h2 class="font-display text-2xl font-semibold mb-4" style="color: var(--warm-900);">Check Your Points</h2>
        <form action="{{ route('rewards.check') }}" method="POST" class="flex flex-col sm:flex-row gap-3">
            @csrf
            <input
                type="email"
                name="email"
                placeholder="Enter your email address"
                value="{{ old('email', $customer->email ?? '') }}"
                required
                class="flex-1 px-4 py-3 rounded-lg border focus:outline-none focus:ring-2"
                style="border-color: var(--warm-300); focus:ring-color: var(--warm-500);"
            >
            <button type="submit" class="btn-primary px-6 py-3 rounded-lg font-semibold text-white" style="background: var(--warm-600);">
                Check Balance
            </button>
        </form>
    </div>

    {{-- Customer Results --}}
    @isset($customer)
    <div class="card p-6 mb-10">
        <h2 class="font-display text-2xl font-semibold mb-4" style="color: var(--warm-900);">
            Welcome back, {{ $customer->name }}!
        </h2>
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="p-4 rounded-lg text-center" style="background: var(--warm-100);">
                <p class="text-3xl font-bold" style="color: var(--warm-800);">{{ number_format($totalPoints) }}</p>
                <p class="text-sm" style="color: var(--warm-600);">Available Points</p>
            </div>
            <div class="p-4 rounded-lg text-center" style="background: var(--warm-100);">
                <p class="text-3xl font-bold" style="color: var(--warm-800);">{{ number_format($lifetimeEarned) }}</p>
                <p class="text-sm" style="color: var(--warm-600);">Lifetime Earned</p>
            </div>
        </div>

        @if($history->count())
        <h3 class="font-display text-lg font-semibold mb-3" style="color: var(--warm-900);">Points History</h3>
        <div class="divide-y" style="border-color: var(--warm-200);">
            @foreach($history as $entry)
            <div class="flex justify-between items-center py-3">
                <div>
                    <p class="font-medium" style="color: var(--warm-900);">{{ $entry->description }}</p>
                    <p class="text-sm" style="color: var(--warm-500);">{{ $entry->created_at->format('M j, Y') }}</p>
                </div>
                <span class="font-semibold {{ $entry->type === 'earned' ? 'text-green-600' : ($entry->type === 'redeemed' ? 'text-red-600' : 'text-yellow-600') }}">
                    {{ $entry->type === 'redeemed' ? '-' : '+' }}{{ number_format($entry->points) }}
                </span>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @endisset

    @if(isset($customer) && !$customer)
    <div class="card p-6 mb-10 text-center">
        <p style="color: var(--warm-700);">We couldn't find an account with that email. Points are earned automatically when your orders are delivered!</p>
    </div>
    @endif

    {{-- Available Rewards --}}
    @if($rewards->count())
    <div class="mb-10">
        <h2 class="font-display text-2xl font-semibold mb-6 text-center" style="color: var(--warm-900);">Available Rewards</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($rewards as $reward)
            <div class="card p-5 text-center {{ isset($totalPoints) && $totalPoints >= $reward->points_required ? 'ring-2' : '' }}" style="{{ isset($totalPoints) && $totalPoints >= $reward->points_required ? 'ring-color: var(--warm-500);' : '' }}">
                <p class="text-3xl mb-2">🎁</p>
                <h3 class="font-display text-lg font-semibold" style="color: var(--warm-900);">{{ $reward->name }}</h3>
                @if($reward->description)
                <p class="text-sm mt-1" style="color: var(--warm-600);">{{ $reward->description }}</p>
                @endif
                <p class="mt-3 font-bold text-lg" style="color: var(--warm-800);">{{ number_format($reward->points_required) }} pts</p>
                <p class="text-sm" style="color: var(--warm-500);">{{ $reward->reward_type_label }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- How It Works --}}
    <div class="card p-8">
        <h2 class="font-display text-2xl font-semibold mb-6 text-center" style="color: var(--warm-900);">How It Works</h2>
        <div class="grid sm:grid-cols-3 gap-6 text-center">
            <div>
                <div class="text-4xl mb-3">🛒</div>
                <h3 class="font-display text-lg font-semibold mb-2" style="color: var(--warm-900);">1. Place an Order</h3>
                <p style="color: var(--warm-700);">Order your favorite baked goods as usual.</p>
            </div>
            <div>
                <div class="text-4xl mb-3">⭐</div>
                <h3 class="font-display text-lg font-semibold mb-2" style="color: var(--warm-900);">2. Earn Points</h3>
                <p style="color: var(--warm-700);">Get {{ $pointsPerDollar }} points for every $1 spent when your order is delivered.</p>
            </div>
            <div>
                <div class="text-4xl mb-3">🎉</div>
                <h3 class="font-display text-lg font-semibold mb-2" style="color: var(--warm-900);">3. Redeem Rewards</h3>
                <p style="color: var(--warm-700);">Use your points for discounts and free treats!</p>
            </div>
        </div>
    </div>

    @endif
</div>
@endsection
